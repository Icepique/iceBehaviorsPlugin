<?php

require_once dirname(__FILE__).'/../../../../test/bootstrap/model.php';
require_once dirname(__FILE__).'/../../lib/PropelActAsEblobBehavior.class.php';

$t = new lime_test(5, new lime_output_color());

$book = new Book();
$book->setTitle('War and Peace');

$t->diag('::setFlag()');

  $book->setFlag('is_active', true);
  $t->todo('Test when implemented');

$t->diag('::setCounter()');

  $book->setCounter('views', 15);
  $t->todo('Test when implemented');

$t->diag('::setEblobElement()');

  $element1 = simplexml_load_string('<multimedia><item id="123456" type="image" slug="n-a" is_primary="1"/></multimedia>', 'IceXMLElement');
  $book->setEblobElement('multimedia', $element1);
  $t->is("<?xml version=\"1.0\"?>\n". $book->getEblobElement('multimedia')->asXml(), trim($element1->asXml()));

  $element2 = simplexml_load_string('<item id="123456" type="image" slug="n-a" is_primary="1"/>', 'IceXMLElement');
  $book->setEblobElement('multimedia', $element2);
  $t->is("<?xml version=\"1.0\"?>\n". $book->getEblobElement('multimedia')->asXml(), trim($element1->asXml()));

  $element3 = simplexml_load_string('<data><item id="123456" type="image" slug="n-a" is_primary="1"/></data>', 'IceXMLElement');
  $book->setEblobElement('multimedia', $element3);
  $t->is("<?xml version=\"1.0\"?>\n". $book->getEblobElement('multimedia')->asXml(), trim($element1->asXml()));
