<?php

class Hotel extends ParserAbstract
{
    private $file;

    public function __construct($mainXMLBlock, $file)
    {
        parent::__construct($mainXMLBlock);
        $this->file = $file;
    }


    /**
     * do something with element
     * @param $element
     * @return mixed
     */
    protected function element($element)
    {
        file_put_contents($this->file, "{$element['Id']['value']}\n", FILE_APPEND);
    }
}