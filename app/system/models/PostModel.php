<?php  defined('HEI_PATH') || exit('No direct script access allowed');

class PostModel extends HEI_Model
{
    /**
     * Suggests tags based on the current user input.
     * This is called via AJAX when the user is entering the tags input.
     */
    public function come()
    {
        var_dump($_GET['id'], 2222);
        //var_dump($this->db()->query('SELECT * FROM test'));
    }
}
