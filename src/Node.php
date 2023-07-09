<?php

namespace InfirSoft\Utils;

use DOMDocument;
use DOMElement;

class Node
{
    private DOMElement $DOMElement;

    public function __construct(DOMDocument $document, string $name, array $attributes = [])
    {
        $this->DOMElement = $document->createElement($name);
        foreach ($attributes as $key => $value) {
            $this->DOMElement->setAttribute($key, $value);
        }
    }

    public function addField(string $name, ?string $value, array $attributes = []): self
    {
        if (!is_null($value)) {
            $field = $this->DOMElement->ownerDocument->createElement($name, $value);
            foreach ($attributes as $key => $value) {
                $field->setAttribute($key, $value);
            }
            $this->DOMElement->append($field);
        }

        return $this;
    }

    public function append(Node $child): self
    {
        $this->DOMElement->append($child->DOMElement);

        return $this;
    }

    public function appendTo(Node $parent): self
    {
        $parent->append($this);

        return $this;
    }

    public function getDOMElement(): DOMElement
    {
        return $this->DOMElement;
    }
}
