<?php

namespace steroids\core\interfaces;

interface IGiiModelAttribute
{
    public function getName();
    public function getCustomProperty($name);
    public function isModelHasOneRelationExists($relationName);
}