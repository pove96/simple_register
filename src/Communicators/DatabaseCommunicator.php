<?php

namespace src\Communicators;

use PDO;
use PDOException;
use src\Person\Person;
use src\Person\PersonValidator;

/**
 * Class DatabaseCommunicator
 * @package src\Communicators
 */
class DatabaseCommunicator
{
    /**
     * @var PersonValidator
     */
    private $personValidator;

    /**
     * DatabaseCommunicator constructor.
     */
    public function __construct()
    {
        $this->personValidator = new PersonValidator();
    }

    /**
     * Imports person to database.
     *
     * @param Person $person
     *
     * @return mixed
     */
    public function insertPerson($person)
    {
        if (!$this->personValidator->validate($person) || $this->personExists($person)) {
            return false;
        }

        $query = "INSERT INTO persons (first_name, last_name, email, phone_no_1, phone_no_2, comment) VALUES (:first_name, :last_name, :email, :phone_no_1, :phone_no_2, :comment)";
        $preparedData = [
            'first_name' => $person->getFirstName(),
            'last_name' => $person->getLastName(),
            'email' => $person->getEmail(),
            'phone_no_1' => $person->getPhoneNumber1(),
            'phone_no_2' => $person->getPhoneNumber2(),
            'comment' => $person->getComment(),
        ];

        return $this->executeQuery($query, $preparedData);
    }

    /**
     * Deletes person from database by his email.
     *
     * @param string $email
     *
     * @return mixed
     */
    public function deletePersonByEmail($email)
    {
        $query = "DELETE FROM persons WHERE email = :email";
        $preparedData = ['email' => $email];

        return $this->executeQuery($query, $preparedData);
    }

    /**
     * Returns person by email.
     *
     * @param string $email
     *
     * @return mixed
     */
    public function findPersonByEmail($email)
    {
        $query = "SELECT * FROM persons WHERE email = :email";
        $preparedData = ['email' => $email];

        return $this->executeQuery($query, $preparedData, true);
    }

    /**
     * Imports persons to database form CSV file. Returns number of imported persons.
     *
     * @param string $path
     *
     * @return int
     */
    public function importPersons($path)
    {
        $personsInserted = 0;
        $handle = fopen($path, "r");

        if (!empty($handle)) {
            while ($data = fgetcsv($handle, 1000, ";")) {
                $person = new Person($data[0], $data[1], $data[2], $data[3], $data[4], $data[5]);
                $result = $this->insertPerson($person);

                if (!empty($result)) {
                    $personsInserted++;
                }
            }
            fclose($handle);
        }

        return $personsInserted;
    }

    /**
     * Checks if person already exists in database.
     *
     * @param Person $person
     *
     * @return mixed
     */
    private function personExists($person)
    {
        return $this->findPersonByEmail($person->getEmail());
    }

    /**
     * Returns PDO connection.
     *
     * @return PDO
     */
    private function getConnection(): PDO
    {
        $conn = new PDO("mysql:host=" . SERVER_NAME . ";dbname=" . DB_NAME, DB_USERNAME, DB_PASSWORD);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $conn;
    }

    /**
     * Executes query to database with prepared data.
     *
     * @param string $query
     * @param array $preparedData
     * @param bool $fetch
     *
     * @return mixed
     */
    private function executeQuery($query, $preparedData, $fetch = false)
    {
        $result = false;

        try {
            $conn = $this->getConnection();
            $statement = $conn->prepare($query);

            if ($fetch) {
                $statement->execute($preparedData);
                $result = $statement->fetch();
            } else {
                $result = $statement->execute($preparedData);
            }

        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }

        $conn = null;

        return $result;
    }
}
