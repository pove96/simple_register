<?php

namespace src\Communicators;

use src\Person\Person;

/**
 * Class UserCommunicator
 * @package src\Communicators
 */
class UserCommunicator
{
    /**
     * @var DatabaseCommunicator;
     */
    private $databaseCommunicator;

    /**
     * UserCommunicator constructor.
     */
    public function __construct()
    {
        $this->databaseCommunicator = new DatabaseCommunicator();
    }

    /**
     * Runs dialog for user to communicate.
     */
    public function run()
    {
        do {
            echo "Actions:\n"
                . "register - register a new person\n"
                . "delete - delete a person from the register\n"
                . "find - find a person in the register\n"
                . "import - import persons using CSV file\n"
                . "exit - end program\n";
            echo "\nChoose your action: ";

            $action = trim(fgets(STDIN));

            $this->processAction($action);

            echo "\n----------------------------------------------------------------------------\n";

        } while ($action != 'exit');
    }

    /**
     * Continues communication with user according to provided action.
     *
     * @param string $action
     */
    private function processAction(string $action)
    {
        switch ($action) {
            case 'register':
                $this->processRegister();
                break;
            case 'delete':
                $this->processDelete();
                break;
            case 'find':
                $this->processFind();
                break;
            case 'import':
                $this->processImport();
                break;
            case 'exit':
                echo "Program ended.";
                break;
            default:
                echo "Wrong action.";
                break;
        }
    }

    /**
     * Continues communication with user for 'register' action.
     */
    private function processRegister()
    {
        echo "Enter first name: ";
        $firstName = trim(fgets(STDIN));
        echo "Enter last name: ";
        $lastName = trim(fgets(STDIN));
        echo "Enter email: ";
        $email = trim(fgets(STDIN));
        echo "Enter phone number: ";
        $phoneNumber1 = trim(fgets(STDIN));
        echo "Enter secondary phone number: ";
        $phoneNumber2 = trim(fgets(STDIN));
        echo "Enter comment: ";
        $comment = trim(fgets(STDIN));

        $person = new Person($firstName, $lastName, $email, $phoneNumber1, $phoneNumber2, $comment);

        $result = $this->databaseCommunicator->insertPerson($person);

        if (!empty($result)) {
            echo "Person registered successfully.";
        } else {
            echo "Error! Probably, person with entered email already exists, try a different one.";
        }
    }

    /**
     * Continues communication with user for 'delete' action.
     */
    private function processDelete()
    {
        echo "Enter email of person you want to delete: ";
        $email = trim(fgets(STDIN));

        $result = $this->databaseCommunicator->deletePersonByEmail($email);

        if (!empty($result)) {
            echo "Person with the provided email has been successfully deleted.";
        } else {
            echo "Error! Person with the the provided email does not exist.";
        }
    }

    /**
     * Continues communication with user for 'find' action.
     */
    private function processFind()
    {
        echo "Enter email of person you want to find: ";
        $email = trim(fgets(STDIN));

        $result = $this->databaseCommunicator->findPersonByEmail($email);

        if (!empty($result)) {
            $firstName = $result['first_name'];
            $lastName = $result['last_name'];
            $email = $result['email'];
            $phoneNumber1 = $result['phone_no_1'];
            $phoneNumber2 = $result['phone_no_2'];
            $comment = $result['comment'];

            $person = new Person($firstName, $lastName, $email, $phoneNumber1, $phoneNumber2, $comment);

            echo $person;
        } else {
            echo "This person does not exist.";
        }
    }

    /**
     * Continues communication with user for 'import' action.
     */
    private function processImport()
    {
        echo "Enter path to CSV file: ";
        $path = trim(fgets(STDIN));

        $result = $this->databaseCommunicator->importPersons($path);

        echo "$result persons imported.";
    }
}
