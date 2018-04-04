<?php

namespace Application;

class Db extends \Util\Db {

    /**
     * @return \Util\Db
     */
    public function __construct() {
        $homepath = getenv('DVM_HOME') ?: \Util\System::homePath();
        $filepath = sprintf('%s%s%s',
                $homepath,
                DIRECTORY_SEPARATOR,
                'mysqlitedb.db'
            );
        
        parent::__construct($filepath);
    }
    
    public function setup() {
        $maxversion = -1;
        try {
            $result = array_shift($this->query('SELECT max(id) as max FROM version', []));
            if($result !== null) {
                $maxversion = $result['max'];
            }
        }
        catch (\Exception $e) {
        }

        switch($maxversion + 1) {
            case 0:
                $this->exec('DROP TABLE version');
                $this->exec('CREATE TABLE version   (date TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL, id INTEGER DEFAULT -1 NOT NULL)');
                $this->exec('CREATE TABLE users     (active INTEGER DEFAULT 0, id INTEGER PRIMARY KEY, name TEXT UNIQUE,   shortname TEXT, password TEXT, realname TEXT)');
                $this->exec('CREATE TABLE functions (active INTEGER DEFAULT 1, id TEXT PRIMARY KEY, name TEXT)');
                $this->exec('CREATE TABLE users_to_courses   (userid INTEGER REFERENCES users(id) ON DELETE CASCADE, courseid INTEGER REFERENCES courses(id) ON DELETE RESTRICT, UNIQUE(userid, courseid))');
                $this->exec('CREATE TABLE users_to_functions (userid INTEGER REFERENCES users(id) ON DELETE CASCADE, functionid TEXT REFERENCES functions(id) ON DELETE RESTRICT, UNIQUE(userid, functionid))');

                $this->exec('CREATE TABLE courses   (active INTEGER DEFAULT 0, id INTEGER PRIMARY KEY, name TEXT, shortname TEXT, type TEXT, CHECK (type = "HS" or type = "BF"), UNIQUE(name, type))');
                $this->exec('CREATE TABLE locations (active INTEGER DEFAULT 0, id INTEGER PRIMARY KEY, name TEXT, shortname TEXT, street TEXT, zipcode TEXT, city TEXT, UNIQUE(name))');
                $this->exec('CREATE TABLE files     (active INTEGER DEFAULT 0, id INTEGER PRIMARY KEY, name TEXT, realpath TEXT UNIQUE NOT NULL)');

                $this->exec('CREATE TABLE status    (active INTEGER DEFAULT 0, id INTEGER PRIMARY KEY, name TEXT UNIQUE NOT NULL)');
                $this->exec('CREATE TABLE notetypes (active INTEGER DEFAULT 1, id INTEGER PRIMARY KEY, type TEXT, definfo TEXT, defremind INTEGER, deffileid INTEGER REFERENCES files(id) ON DELETE RESTRICT)');
                $this->exec('CREATE TABLE notetypes_courses (notetypeid INTEGER REFERENCES notetypes(id) ON DELETE CASCADE, courseid INTEGER REFERENCES courses(id) ON DELETE RESTRICT)');

                $this->exec('CREATE TABLE persons           (active INTEGER DEFAULT 0, id INTEGER PRIMARY KEY, givenname TEXT, surename TEXT, birthname TEXT, gender INTEGER, birth TIMESTAMP, birthplace TEXT, nationality TEXT, firstseen TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL, photo INTEGER REFERENCES files(id) ON DELETE RESTRICT, created TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL, modified TIMESTAMP, createdby INTEGER REFERENCES users(id) ON DELETE RESTRICT, modifiedby INTEGER REFERENCES users(id) ON DELETE RESTRICT)');
                $this->exec('CREATE TABLE persons_emails    (active INTEGER DEFAULT 0, id INTEGER PRIMARY KEY, personid INTEGER REFERENCES persons(id) ON DELETE CASCADE, address TEXT, hint TEXT, prefer INTEGER, created TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL, modified TIMESTAMP, createdby INTEGER REFERENCES users(id) ON DELETE RESTRICT, modifiedby INTEGER REFERENCES users(id) ON DELETE RESTRICT, UNIQUE(personid, address))');
                $this->exec('CREATE TABLE persons_phones    (active INTEGER DEFAULT 0, id INTEGER PRIMARY KEY, personid INTEGER REFERENCES persons(id) ON DELETE CASCADE, number TEXT, hint TEXT, prefer INTEGER, created TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL, modified TIMESTAMP, createdby INTEGER REFERENCES users(id) ON DELETE RESTRICT, modifiedby INTEGER REFERENCES users(id) ON DELETE RESTRICT, UNIQUE(personid, number))');
                $this->exec('CREATE TABLE persons_addresses (active INTEGER DEFAULT 0, id INTEGER PRIMARY KEY, personid INTEGER REFERENCES persons(id) ON DELETE CASCADE, co TEXT, street TEXT, zipcode TEXT, city TEXT, country TEXT, hint TEXT, prefer INTEGER, created TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL, modified TIMESTAMP, createdby INTEGER REFERENCES users(id) ON DELETE RESTRICT, modifiedby INTEGER REFERENCES users(id) ON DELETE RESTRICT)');
                $this->exec('CREATE TABLE persons_activities (active INTEGER DEFAULT 0, id INTEGER PRIMARY KEY, personid INTEGER REFERENCES persons(id) ON DELETE CASCADE, type TEXT NOT NULL, courseid INTEGER REFERENCES courses(id) ON DELETE RESTRICT, locationid INTEGER REFERENCES locations(id) ON DELETE RESTRICT, start TIMESTAMP, end TIMESTAMP, statusid INTEGER DEFAULT 0 REFERENCES status(id) ON DELETE RESTRICT, created TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL, modified TIMESTAMP, createdby INTEGER REFERENCES users(id) ON DELETE RESTRICT, modifiedby INTEGER REFERENCES users(id) ON DELETE RESTRICT)');
                $this->exec('CREATE TABLE persons_activities_notes (active INTEGER DEFAULT 0, id INTEGER PRIMARY KEY, personid INTEGER REFERENCES persons(id) ON DELETE CASCADE, activityid INTEGER REFERENCES persons_activities(id)  ON DELETE CASCADE, remind  TIMESTAMP, info TEXT, typeid INTEGER REFERENCES notetypes(id) ON DELETE RESTRICT, created TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL, modified TIMESTAMP, createdby INTEGER REFERENCES users(id) ON DELETE RESTRICT, modifiedby INTEGER REFERENCES users(id) ON DELETE RESTRICT, fileid INTEGER REFERENCES files(id) ON DELETE RESTRICT)');

                $this->exec('INSERT INTO status (active, id, name) VALUES (1, 0, \'in Bearbeitung\')');
                $this->exec('INSERT INTO status (active, id, name) VALUES (1, 1, \'abgeschlossen\')');
                $this->exec('INSERT INTO status (active, id, name) VALUES (1, 2, \'keine Rückmeldung\')');
                $this->exec('INSERT INTO status (active, id, name) VALUES (1, 3, \'Keine Finanzierung\')');
                $this->exec('INSERT INTO status (active, id, name) VALUES (1, 4, \'anderer Grund\')');

                $this->exec('INSERT INTO functions (id, name) VALUES (\'trainings\', \'Praktikanten\')');
                $this->exec('INSERT INTO functions (id, name) VALUES (\'inquries\', \'Interessenten\')');
                $this->exec('INSERT INTO functions (id, name) VALUES (\'applications\', \'Bewerber\')');
                $this->exec('INSERT INTO functions (id, name) VALUES (\'studies\', \'Studenten\')');
                $this->exec('INSERT INTO functions (id, name) VALUES (\'settings\', \'Einstellungen\')');
                $this->exec('INSERT INTO functions (id, name) VALUES (\'create\', \'Personen anlegen\')');

                $this->exec('INSERT INTO notetypes (type) VALUES (\'interne Notiz\')');
                $this->exec('INSERT INTO notetypes (type, defremind) VALUES (\'Frage an uns\', 0)');
                $this->exec('INSERT INTO notetypes (type, defremind) VALUES (\'Info-Material\', 14)');
                $this->exec('INSERT INTO notetypes (type, defremind) VALUES (\'Vertrag\', 14)');
                $this->exec('INSERT INTO notetypes (type) VALUES (\'Matrikelnummer\')');
                $this->exec('INSERT INTO notetypes (type, defremind) VALUES (\'Frage von uns\', 14)');

                $this->exec('INSERT INTO locations VALUES (1, 1, "Schwerin", "SN", "ABC-Straße", "19001", "Schwerin")');

                $this->exec('INSERT INTO users (active, id, name, password, realname) VALUES (1, 1, \'mko\', \'' . password_hash('secret', PASSWORD_DEFAULT) . '\', \'Martin\')');
                $this->exec('INSERT INTO users_to_functions (userid, functionid) VALUES (1, \'settings\')');

//                $hash = password_hash('schule', PASSWORD_DEFAULT);
//                $this->exec('INSERT INTO users (active, id, name, password, realname) VALUES (1, 2, \'vitruvius\', \'' . $hash . '\', \'Vitruvius\')');
                
                $this->exec('INSERT INTO version (id) VALUES (0);');
            case 1:
                $this->exec('CREATE INDEX idx_courses_id ON courses (id);');
                $this->exec('INSERT INTO version (id) VALUES (1);');
        }

        error_log(print_r($maxversion,true));
    }
}
