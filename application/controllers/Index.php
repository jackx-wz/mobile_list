<?php
class IndexController extends Yaf_Controller_Abstract{
    public $files = [];
    public $seg = 'xdccdx';
    public $port = 80;

    private function init(){
        $this->get_files(FILES_PATH);
        $this->host = in_array($_SERVER['SERVER_PORT'], [443, 80]) ? $_SERVER['HTTP_HOST'] : $_SERVER['HTTP_HOST'].':'.$_SERVER['SERVER_PORT'];
    }

    public function indexAction(){
        $pkgs = [
            'andr' => [],
            'ios'  => [],
            'host' => $this->host,
            'seg'  => $this->seg,
            'port' => $this->port,
        ];

        $this->init();

        foreach($this->files as $i => $file){
            $info = pathinfo($file);
            $dir_name = substr(str_replace(FILES_PATH, '', $info['dirname']), 1);
            $file_info = [
                'file'      => $file,
                'base_name' => $info['basename'],
                'dir_name'  => $dir_name,
                'file_name' => $info['filename'],
                'time'      => date('Y-m-d H:i:s', filectime($file)),
                'ctime'     => filectime($file),
            ];

            if($info['extension'] == 'ipa'){
                $pkgs['ios'][$file_info['ctime']] = $file_info;
            } else if($info['extension'] == 'apk'){
                $pkgs['andr'][$file_info['ctime']] = $file_info;
            }
        }

        krsort($pkgs['ios']);
        krsort($pkgs['andr']);

        $this->getView()->assign($pkgs);
    }

    public function plistAction($file_name){
        $info = explode($this->seg, $file_name);
        Yaf_Dispatcher::getInstance()->disableView();
        $this->getView()->display(APP_PATH.'/public/template.plist', [
            'file_name' => $info[0],
            'dir_name'  => $info[1],
            'host'      => $this->host,
        ]);
    }

    public function uploadAction(){
        if(empty($_POST) && empty($_FILES)){
            $this->getView()->assign([
                'host'      => $this->host,
            ]);
        } else if(isset($_POST['submit'])){
            $rst = $this->upload_file();
            $this->getView()->assign([
                'host'      => $this->host,
                'rst'       => $rst,
            ]);
        } else{
            Yaf_Dispatcher::getInstance()->disableView();
            echo $this->upload_file();
        }
    }

    private function upload_file(){

        if(empty($_FILES)){
            return "no file";
        } else if ($_FILES["file"]["error"] > 0){
            return "Return Code: " . $_FILES["file"]["error"] . "<br />";
        } else{
            $name_info = pathinfo($_FILES["file"]['name']);

            if(in_array($name_info['extension'], ['apk', 'ipa'])){
                $this->move_file($_FILES["file"]);

                return "success";
            } else{
                return "can't upload this file!";
            }
        }
    }

    private function move_file($file_info){
        $file_name = $file_info["name"];

        if (file_exists(FILES_PATH . "/" . $file_info["name"])){
            $file_name = $this->change_file_name($file_info["name"]);
        }

        move_uploaded_file($file_info["tmp_name"], FILES_PATH . "/" . $file_name);
    }

    private function change_file_name($name){
        $name_info = pathinfo($name);
        $file_name = $name_info['filename'];
        preg_match('/(.+)_\[(\d+)\]$/', $file_name, $m);

        if(isset($m[2])){
            $file_name = $m[1].'_['.++$m[2].'].'.$name_info['extension'];
        } else{
            $m[1] = $file_name;
            $m[2] = 1;
            $file_name =  $file_name.'_[1].'.$name_info['extension'];
        }

        while(file_exists(FILES_PATH . "/" . $file_name)){
            $file_name = $m[1].'_['.++$m[2].'].'.$name_info['extension'];
        }

        return $file_name;
    }

    private function get_files($dir){
        $files = scandir($dir);

        foreach($files as $f){
            $new_f = $dir.'/'.$f;
            if(is_file($new_f)){
                $this->files[] = $new_f;
            } else if(($f != '.') && ($f != '..')){
                $this->get_files($new_f);
            }
        }
    }
}
