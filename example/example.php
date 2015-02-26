<?php

include __DIR__ . '/../ParserAbstract.php';
include __DIR__ . '/Hotel.php';

$hotel = new Hotel('Hotel', 'hotel.log');
$hotel->load('hotel.xml');