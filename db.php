<?php

class DB{
    public function __construct($host, $db, $user, $password, $table_name="parser_data")
    {
      $this->conn = mysqli_connect($host, $user, $password, $db);
      $this->tablename = $table_name;
      $this->createTable();
    }

    function __destruct()
    {
      mysqli_close($this->conn);
    }

    public function query($query)
    {
      if(mysqli_query($this->conn, $query)){
        return true;
      }
      echo "\n";
      echo $query . " : ";
      echo mysqli_error($this->conn);
      echo "\n";
      return false;
    }
    public function dropTable(){
      $query = 'DROP TABLE IF EXISTS `' . $this->tablename . '`';
      $this->query($query);
    }

    public function createTable()
    {
      $query = "CREATE TABLE IF NOT EXISTS `{$this->tablename}` " .
        "(`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `model_id` int(11) NOT NULL, `review_id` int(11) NOT NULL UNIQUE, " .
       "`review_good` text, `review_bad` text, `review_comment` text, `review_grade` int(11), " .
        "`review_author` varchar(255), `review_date` date) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
      $this->query($query);
    }

    private function createValues($reviewObj){
      return "SELECT {$reviewObj['model_id']}, {$reviewObj['review_id']}, '{$reviewObj['review_good']}', '{$reviewObj['review_bad']}', '{$reviewObj['review_comment']}', {$reviewObj['review_grade']}, '{$reviewObj['review_author']}', '{$reviewObj['review_date']}'";
    }

    public function addReview($reviewObj)
    {
      $query = "INSERT INTO `{$this->tablename}` (`model_id`, `review_id`, `review_good`, `review_bad`, `review_comment`, `review_grade`, `review_author`, `review_date`) {$this->createValues($reviewObj)} WHERE NOT EXISTS (SELECT review_id FROM {$this->tablename} WHERE review_id = {$reviewObj['review_id']}) LIMIT 1";
      $this->query($query);
    }

    public function addReviews($reviews)
    {
      foreach($reviews as $review) {
        $this->addReview($review);
        echo "review added\n";
      }
    }
}
