<?php
assert($this instanceof Qiq);
$this->setLayout('layout/base');
?>
Greeting: {{h foo($this->greeting) }}
