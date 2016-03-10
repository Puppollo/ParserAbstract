<?php

/**
 * Class ParserAbstract
 * Provide a simple way for parsing big files
 *
 * https://github.com/Puppollo/ParserAbstract.git
 */
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
            if ($reader->nodeType == XMLReader::END_ELEMENT && $reader->localName == $element) {
                $count++;
            }
        }
        $reader->close();
        return $count;
    }

    /**
     * @return mixed
     */
    public function getBlock()
    {
        return $this->mainXMLBlock;
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

        $count = 0;
        $currentBlock = 0;

        while ($reader->read()) {

            if ($reader->nodeType == XMLReader::ELEMENT && $reader->localName == $this->mainXMLBlock) {

                if ($currentBlock < $offset) {
                    $currentBlock++;
                    continue;
                }

                $this->element(simplexml_load_string($reader->readOuterXml()));

                $count++;

                if ($amount > 0 && $count >= $amount) {
                    $reader->close();
                    return $count;
                }
            }
        }
        $reader->close();
        return $count;
    }
}