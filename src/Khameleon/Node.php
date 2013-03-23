<?php

namespace Khameleon;

interface Node
{
    public function getPath();
    public function getName();
    public function remove();
}