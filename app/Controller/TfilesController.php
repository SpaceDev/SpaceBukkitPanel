<?php

/**
*
*   ####################################################
*   FilesController.php 
*   ####################################################
*
*   DESCRIPTION
*
*   This controller manages the File Manager
*   
*   
* @copyright  Copyright (c) 20011 XereoNet and SpaceBukkit (http://spacebukkit.xereo.net)
* @version    Last edited by Antariano
*
*/

class TfilesController extends AppController {

    public $helpers = array('Html','Form');

    public $components = array('RequestHandler');

    public $name = 'TfilesController';

    function index() {
 
        if (!isset($api)){
            require APP . 'spacebukkitcall.php';  
        }

        $args = array();   
        $running = $api->call("isServerRunning", $args, true);
        
        $this->set('running', $running);

        //IF "FALSE", IT'S STOPPED. IF "NULL" THERE WAS A CONNECTION ERROR

        if (is_null($running)) {

        $this->layout = 'sbv1_notreached'; 
                     
        } else {

            $args = array();
            
            $this->layout = 'sbv1';  

            $this->set('title_for_layout', 'File Manager');
        }          

    }

    //load directory

    function loadDir($path) {

        if ($this->request->is('ajax')) {

            $this->disableCache();
            //Configure::write('debug', 0);
            $this->autoRender = false;

            require APP . 'spacebukkitcall.php';

            $p =  str_replace("@@", "/", $path);


            $args = array($p);

            $files = $api->call('listFilesAndDirectories', $args, true);

            //config

            echo '<div id="config" data-path="'.$p.'"></div>';
            echo '<h3>'.$p.'</h3>';

            //back button

            if ($p != './') {

                $link = substr($p, 0, strrpos($p, "/"));
                $link =  str_replace("/", "@@", $link);

                if ($link == '.') { $link .= '@@'; }

                echo '<section class="f-row" id="back" data-p="$filepath"><a href="'.$link.'" class="button icon arrowleft explore">Go back one dir...</a></section>';

            }

            //Parse worldlist and add backup buttons
            foreach($files as $file) {

                $args = array($p . '/' . $file);

                $data = $api->call('getFileInformations', $args, true);

                $filename = $data['Name'];
                $filesize = $data['Size'];
                $filemime = $data['Mime'];

                // set custom mime types

                $ext = end(explode(".", $filename));

                if ($data['IsFile'] && ($ext == 'log' || $ext == 'yml' || $ext == 'properties')) {

                    $filemime = 'text/plain';

                }

                elseif ($data['IsFile'] && ($ext == 'jar')) {

                    $filemime = 'java/jar';

                }

                elseif ($data['IsFile']) {

                    $filemime = 'null';

                }

                //set image and type according to type
                $filepath =  str_replace("/", "@@", $data['Path']);

                if ($data['IsDirectory']) {

                    $fileimage = 'folder.png';
                    $type = 'dir';
                    $filemime = 'folder';
                    $factions = '<span class="button-group"><a class="button icon arrowright explore" href="'.$filepath.'">Explore</a></span>';

                } else {

                    $fileimage = str_replace("/", "-", $filemime).'.png';
                    $type = 'file';
                    $factions = '';

                }


                echo <<<END

                <section class="f-row" data-type="$type" data-name="$filename" data-size="$filesize" data-path="$filepath" data-mime="$filemime" data-img="$fileimage">
                    <div class="f-filename">
                        <img src="./filemanager/16/$fileimage" />
                        $filename
                    </div>
                    <div class="f-mime">
                        $filesize KB, $filemime
                    </div>
                    <div class="f-btns">

                        $factions

                        <span class="button-group">
                            <a class="button icon log" href="#">Edit</a>
                            <a class="button icon move" href="#">Move</a>
                            <a class="button icon edit" href="#">Rename</a>
                            <a class="button icon remove danger" href="#">Delete</a>               
                        </span>

                    </div>
                </section>

END;

            }

        }

    }

    //load tree view of a directory

    function loadTree() {

        if ($this->request->is('ajax')) {

            $this->disableCache();
            //Configure::write('debug', 0);
            $this->autoRender = false;

            require APP . 'spacebukkitcall.php';

            $path = urldecode($this->params['url']['path']);

            //construct path

            $p =  str_replace("@@", "/", $path);

            $args = array($p);

            $dirs = $api->call('listDirectories', $args, true);

            $data = array();

            foreach ($dirs as $n => $dir) {

                $full = $p . '/' . $dir;

                $full =  str_replace("/", "@@", $full);
                $full =  str_replace("@@@@", "@@", $full);

                $data[$n] = array(

                    'attr'  => array('data-path' => $full, 'id' => $full),
                    'data'  => $dir,
                    'state' => 'closed'

                );

            }

            //output the json encoded object

            echo json_encode($data);

        }

    }

    //load file

    //move file

    //delete file

    //rename file

    //create file

    //create folder

    //move folder

    //delete folder

    //rename folder

    //upload file

    //download file

    //unzip

}
