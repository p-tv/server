<?php

namespace ptv\model;


class ChannelRestriction implements ModelHydration {

    var $id;
    var $channelId;
    var $enabled;
    var $name;
    var $value;

    function fromArray(array $arr) {
        $this->channelId = $arr['channelId'];
        $this->id = $arr['id'];
        $this->name = $arr['name'];
        $this->enabled = $arr['enabled'];
        $this->value = $arr['value'];
    }

    function getInsertSQL(): string {
        return 'insert into channel_restriction (channelId, name, enabled, value) values (:channelId, :name, :enabled, :value)';
    }

    function getInsertParameters(): array {
        return [
            ':name' => $this->name,
            ':channelId' => $this->channelId,
            ':enabled' => $this->enabled,
            ":value" => $this->value
        ];
    }

    /**
     * Gets the update SQL
     * @return string
     */
    function getUpdateSQL():string {
        return "update channel_restriction set name = :name, enabled = :enabled, channelId = :channelId, value = :value where id = :id";
    }

    /**
     * Array of parameters mapping to the updateSQL
     * @return array
     */
    function getUpdateParameters(): array {
        $params = $this->getInsertParameters();
        $params[':id'] = $this->id;
        return $params;
    }


}