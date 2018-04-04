<?php

namespace Application\Requests\Settings;

abstract class Templates extends \Application\Requests\Settings {

    /**
     * @var string|null Key in $options where a unhandeled path fragment should be loaded in
     */
    protected static $optionNames = [ 'templateid' ];
    
    /**
     * @var string|null Current course id
     */
    protected $objectId = null;

    /**
     * @var string Permission identifier
     */
    protected $requiredPermission = 'templates';
    
    /**
     * @param \Util\Http\RestHandler $request
     * @param array $options
     */
    public function __construct($request, $options) {
        parent::__construct($request, $options);
        
        if(array_key_exists('templateid', $options)) {
            $this->objectId = $options['templateid'];
        }
    }

    /**
     * @return array
     */
    protected function getObjects() {
        return $this->db->query(
            'SELECT id, name FROM templates',
            array()
        );
        
    }

    /**
     * @param string $templateId
     * @return array|null
     */
    protected function getObject($templateId) {
        
        $result = $this->db->query(
            'SELECT id, name FROM templates WHERE id = :id', 
            array(
                'id' => $templateId
            )
        );
        $object = array_shift($result);
        unset($result);
        
        $files = $this->db->query(
            'SELECT files.name AS filename FROM files JOIN templates_to_files ON templates_to_files.id=files.id WHERE templates_to_files.templateid = :templateid', 
            array(
                'templateid' => $templateId
            )
        );
        
        return $object
            ? array('id' => $object['id'],
                    'name' => $object['name'],
                    'files' => $files)
            : null;
    }
    
    /**
     * @param string $templateId
     */
    protected function deleteObject($templateId) {

        $tid = $this->db->startTransaction();
        
        $curFiles = [];
        try {
            $curFiles = $this->db->query(
                'SELECT files.id, files.realpath FROM files JOIN templates_to_files ON templates_to_files.id = files.id WHERE templates_to_files.templateid = :id', 
                array(
                    'id' => $templateId
                ));

            $this->db->delete(
                    'templates_to_files',
                    array( 'templateid' => $templateId )
                );

            foreach($curFiles as $entry) {
                $this->db->delete(
                        'files',
                        array( 'id' => $entry['id'] )
                    );
            }

            $this->db->delete(
                    'templates',
                    array('id' => $templateId)
                );
        }
        catch (\Exception $e) {
            $this->db->rollbackTransaction($tid);
            throw $e;
        }
        
        $this->db->commitTransaction($tid);
        
        foreach($curFiles as $entry) {
            $filename = $entry['realpath'];
            if(file_exists($filename)) {
                unlink($filename);
            }
        }
    }
    
    /**
     * @param array $data Data required to create new course item
     * @return array|null
     * @throws \Util\Http\Exceptions\BadRequest
     */
    protected function createObject($data) {
        if(count($data) !== 1 || !array_key_exists('name', $data)) {
            throw new \Util\Http\Exceptions\BadRequest('unexpected data');
        }

        //@todo: UNIQUE constraint failed ... 500?
        $rowId = $this->db->insert(
                'templates',
                array( 
                    'name' => $data['name']
                )
            );
        
        return $this->getObject($rowId);
    }
    
    /**
     * @param string $templateId
     * @param array $data
     * @throws \Util\Http\Exceptions\BadRequest
     * @throws \Exception
     */
    protected function updateObject($templateId, $data) {

        $allowedNames = [ 'name', 'files' ];
        if( count(array_diff(array_keys($data), $allowedNames)) > 0) {
            throw new \Util\Http\Exceptions\BadRequest('unexpected data');
        }

        $newFiles = $oldFiles = [];
        $tid = $this->db->startTransaction();
        
        try {
            if(array_key_exists('name', $data)) {
                $this->db->update(
                        'templates', 
                        array( 'id' => $templateId ), 
                        array( 'name' => $data['name'])
                    );
            }

            /*
             * if there are new file sent, query existing files and delete db entries 
             * (files will be removed after commit)
             */
            if(array_key_exists('files', $data)) {
                $oldFiles = $this->db->query(
                        'SELECT realpath FROM files JOIN templates_to_files ON templates_to_files.id = files.id WHERE templates_to_files.templateid = :id', 
                        array(
                            'id' => $templateId
                        ));

                $this->db->delete(
                        'templates_to_files',
                        array( 'templateid' => $templateId )
                    );
            }
        
            /*
             * loop through files and save them to disk and database
             * 
             * store list of written files in case an exception occurs
             * requiring the newly files to be removed again.
             */
            foreach($data['files'] as $array) {
                if(array_key_exists('name', $array) && array_key_exists('data', $array)) {
                    $realpath = tempnam(\Util\System::homePath(), '');

                    if($realpath === false) {
                        throw new \Exception('Writing to disk failed.');
                    }

                    $newFiles[] = $realpath;
                    file_put_contents($realpath, $array['data']);

                    $rowId = $this->db->insert(
                            'files',
                            array( 
                                'name' => $array['name'],
                                'realpath' => $realpath
                            )
                        );
                    
                    $this->db->insert(
                            'templates_to_files',
                            array( 
                                'templateid' => $templateId,
                                'id' => $rowId
                            )
                        );
                }
            }
        }
        catch (\Exception $e) {
            $this->db->rollbackTransaction($tid);
            
            foreach($newFiles as $filename) {
                if(file_exists($filename)) {
                    unlink($filename);
                }
            }

            throw $e;
        }
        
        $this->db->commitTransaction($tid);
        
        foreach($oldFiles as $oldFile) {
            $filename = $oldFile['realpath'];
            if(file_exists($filename)) {
                unlink($filename);
            }
        }
    }
}