<?php

namespace Infirsoft\Utils;

use DOMDocument;
use DOMElement;

abstract class DOMDocumentBuilder
{
    private DOMDocument $dom;

    final public function __construct(
        protected object $model
    )
    {
        $this->dom = new DOMDocument('1.0', 'utf-8');
        $this->dom->preserveWhiteSpace = false;
        $this->dom->formatOutput = true;

        if (($body = $this->index()) instanceof Node) {
            $body = $body->getDOMElement();
        }

        $this->appendElement($this->dom, $body);
    }

    abstract public function index(): Node|DOMElement;

    public function get(): DOMDocument
    {
        $this->dom->loadXML($this->dom->saveXML());

        return $this->dom;
    }

    protected function createElement(string $name, array $attributes = []): DOMElement
    {
        $el = $this->dom->createElement($name);
        foreach ($attributes as $key => $value) {
            $el->setAttribute($key, $value);
        }

        return $el;
    }

    protected function appendField(DOMElement $to, string $name, ?string $value, array $attributes = []): void
    {
        if (is_null($value)) {
            return;
        }

        $el = $this->dom->createElement($name, $value);
        foreach ($attributes as $key => $value) {
            $el->setAttribute($key, $value);
        }

        $to->append($el);
    }

    protected function appendElement(DOMDocument|DOMElement $to, DOMElement $element): void
    {
        if ($element->nodeValue !== '') {
            $to->append($element);
        }
    }

    protected function createNode(string $name, array $attributes = []): Node
    {
        return new Node($this->dom, $name, $attributes);
    }
}
