<?php
class PasswordManager
{
  private $db;

  public function __construct()
  {
    $this->db = new SQLite3('passwords.db');
    $this->createTable();
  }

  private function createTable()
  {
    $query = "CREATE TABLE IF NOT EXISTS passwords (
              id INTERGER PRIMARY KEY,
              website TEXT NOT NULL,
              username TEXT NOT NULL,
              password TEXT NOT NULL
    )";
    $this->db->exec($query);
  }

  private function addPassword()
  {
    $website = readline("Enter website: ");
    $username = readline("Enter username: ");
    $password = readline("Enter password: ");
    $encryptedPassword = $this->encryptPassword($password);
    $query = "INSERT INTO passwords (website, username, password) VALUES ('$website', '$username', '$encryptedPassword')";
    $this->db->exec($query);
    echo "Password added successfully.\n";
  }

  private function encryptPassword($password)
  {
    $key = 'e613ea9d53ded0d1834b877a55071c3eec5ba9556527144aa9829612f3682dda';
    $cipher = "aes-256-cbc";
    $iv_length = openssl_cipher_iv_length($cipher);
    $iv = openssl_random_pseudo_bytes($iv_length);
    $encrypted = openssl_encrypt($password, $cipher, $key, OPENSSL_RAW_DATA, $iv);
    return base64_encode($iv . $encrypted);
  }

  private function decryptPassword($encryptedPassword)
  {
    $key = 'e613ea9d53ded0d1834b877a55071c3eec5ba9556527144aa9829612f3682dda';
    $cipher = "aes-256-cbc";
    $iv_length = openssl_cipher_iv_length($cipher);
    $encryptedPassword = base64_decode($encryptedPassword);
    $iv = substr($encryptedPassword, 0, $iv_length);
    $encrypted = substr($encryptedPassword, $iv_length);
    return openssl_decrypt($encrypted, $cipher, $key, OPENSSL_RAW_DATA, $iv);
  }


  private function viewPasswords()
  {
    $query = "SELECT * FROM passwords";
    $result = $this->db->query($query);
    if (!$result || $result->numColumns() === 0)
    {
      echo "No passwords saved yet.\n";
      return;
    }
    echo "Saved Passwords:\n";
    while ($row = $result->fetchArray(SQLITE3_ASSOC))
    {
      $decryptedPassword = $this->decryptPassword($row['password']);
      echo "Website: {$row['website']}, Username: {$row['username']}, Password: {$decryptedPassword}\n";
    }
  }

  private function printWhiteSpace()
  {
    for ($x = 0; $x < 100; $x++)
    {
      echo "\n";
    }
  }

  public function main()
  {
    echo "Welcome to Simple Password Manager\n";
    while (True)
    {
      echo "\nOptions:\n";
      echo "1. Add a new password\n";
      echo "2. View saved passwords\n";
      echo "3. Exit\n";
      $choice = readline("Enter your choice: ");
      switch ($choice)
      {
        case '1':
          $this->printWhiteSpace();
          $this->addPassword();
          break;
        case '2':
          $this->printWhiteSpace();
          $this->viewPasswords();
          break;
        case '3':
          $this->printWhiteSpace();
          echo "Exiting...\n";
          exit;
        default:
          $this->printWhiteSpace();
          echo "Invalid choice. Please try again.\n";
      }
    }
  }
}

$passwordManager = new PasswordManager();
$passwordManager->main();
?>