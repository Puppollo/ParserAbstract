<?php

abstract class ParserAbstract
{
    private $mainXMLBlock; // xml entity to work with

    public function __construct($mainXMLBlock)
    {
        $this->mainXMLBlock = $mainXMLBlock;
    }

    /**
     * Count entities
     * @param string $file
     * @param string $element
     * @return boolean|int
     */
    public static function count($file, $element)
    {
        $reader = new XMLReader();
        if (!$reader->open($file)) {
            echo "ERROR: file open error {$file}\n";
            return false;
        }

        $count = 0;
        while ($reader->read()) {
            if ($reader->nodeType == XMLReader::END_ELEMENT) {
                if ($reader->localName == $element) {
                    $count++;
                }
            }
        }
        $reader->close();
        return $count;
    }

    /**
     * do something with element
     * @param $element
     * @return mixed
     */
    protected abstract function element($element);

    /**
     * Parse xml file
     * @param string $file
     * @param int $offset
     * @param int $amount
     * @return boolean|int
     */
    public function load($file, $offset = 0, $amount = -1)
    {
        $reader = new XMLReader();
        if (!$reader->open($file)) {
            echo "ERROR: file open error {$file}\n";
            return false;
        }

        $object = null;
        $element = null;
        $count = 0;
        $currentBlock = 0;
        $skip = false;

        while ($reader->read()) {
            if ($reader->nodeType == XMLReader::ELEMENT) {
                $element = $reader->localName;
                if ($reader->localName == $this->mainXMLBlock) {
                    $skip = false;
                    $object = [];
                }
                if (!$skip) {
                    if ($reader->hasAttributes) {
                        while ($reader->moveToNextAttribute()) {
                            if (isset($object[$element]['attributes'][$reader->name])) {
                                if (is_array($object[$element]['attributes'][$reader->name])) {
                                    $object[$element]['attributes'][$reader->name][] = $reader->value;
                                } else {
                                    $object[$element]['attributes'][$reader->name] = [$reader->value];
                                }
                            } else {
                                $object[$element]['attributes'][$reader->name] = $reader->value;
                            }

                        }
                    }
                }
            } elseif ($reader->nodeType == XMLReader::TEXT || $reader->nodeType == XMLReader::CDATA || $reader->nodeType == XMLReader::WHITESPACE || $reader->nodeType == XMLReader::SIGNIFICANT_WHITESPACE) {
                if (!$skip && !isset($object[$element]['value'])) {
                    $object[$element]['value'] = $reader->value;
                }
            } elseif ($reader->nodeType == XMLReader::END_ELEMENT) {
                if ($reader->localName == $this->mainXMLBlock) {
                    $skip = true;
                    if ($currentBlock < $offset) {
                        $currentBlock++;
                        continue;
                    }

                    $this->element($object);

                    $count++;
                    $object = null;

                    if ($amount > 0) {
                        if ($count >= $amount) {
                            $reader->close();
                            return $count;
                        }
                    }
                } elseif (!$skip) {

                }
            }
        }
        $reader->close();
        return $count;
    }
}