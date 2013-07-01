<?php  defined('HEI_PATH') || exit('No direct script access allowed');

class PostController extends HEI_Controller
{
    /**
     * Suggests tags based on the current user input.
     * This is called via AJAX when the user is entering the tags input.
     */
    public function actionIndex()
    {
        var_dump($_GET['b']);
        //$this->model('post')->come();
    }
}
