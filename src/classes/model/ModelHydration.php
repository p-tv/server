<?php

namespace ptv\model;


interface ModelHydration {

    function fromArray(array $arr);
    function getInsertSQL(): string;
    function getInsertParameters(): array;
    function getUpdateSQL(): string;
    function getUpdateParameters(): array;
}