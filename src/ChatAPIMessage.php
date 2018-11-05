<?php

namespace NotificationChannels\ChatAPI;

class ChatAPIMessage
{
    public $to;
    public $msg;
    public $file;
    public $file_name;
    public $file_mimetype;

    public static function create($msg = null)
    {
        return new static($msg);
    }

    public function __construct($msg = null)
    {
        $this->msg($msg);
    }

    public function to($to)
    {
        $this->to = $to;
        return $this;
    }

    public function msg($msg)
    {
        $this->msg = $msg;
        return $this;
    }


    public function file($file,$filename=null,$mimetype=null)
    {
        $this->file = $file;
        $this->file_name = $filename;
        $this->file_mimetype = $mimetype;
        return $this;
    }

    public function content($content)
    {
        $this->msg($content);
        return $this;
    }

    public function toArray()
    {
        return [
            'to'             => $this->to,
            'msg'            => $this->msg,
            'file'           => $this->file === null ? false : [
                'body'  => $this->file,
                'filename' => $this->file_name,
                'mimetype' => $this->file_mimetype
            ]
        ];
    }
}
