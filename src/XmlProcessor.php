<?php

namespace Infirsoft\Utils;

use DOMDocument;
use DOMNode;
use Infirsoft\Utils\Exceptions\InvalidXmlException;

class XmlProcessor
{
    /**
     * @param string $schemaPath
     * @param object|null $dto
     * @param array $attributes
     * @return array
     */
    public static function loadSchema(string $schemaPath, $dto = null, array $attributes = []): array
    {
        if ($dto) {
            foreach (get_object_vars($dto) as $key => $value) {
                $attributes['{' . $key . '}'] = $value;
            }
        }

        return json_decode(strtr(file_get_contents($schemaPath), $attributes), true);
    }

    /**
     * @param array|string $schema
     * @return DOMDocument
     */
    public static function convertSchema($schema): DOMDocument
    {
        $dom = new DOMDocument('1.0', 'utf-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;

        $schema = is_string($schema) ? json_decode($schema, true) : $schema;
        self::applyNodes($dom, $schema);
        $dom->loadXML($dom->saveXML());

        return $dom;
    }

    /**
     * @param DOMDocument $dom
     * @param string $xsdPath
     * @return void
     * @throws InvalidXmlException
     */
    public static function validate(DOMDocument $dom, string $xsdPath): void
    {
        libxml_use_internal_errors(true);
        if (!@$dom->schemaValidate($xsdPath)) {
            $error = isset(libxml_get_errors()[0]) ? libxml_get_errors()[0]->message : '';
            libxml_clear_errors();
            throw new InvalidXmlException($error);
        }
    }

    /**
     * @param DOMDocument|DOMNode $parent
     * @param array $schema
     * @return void
     * @throws \DOMException
     */
    protected static function applyNodes($parent, array $schema): void
    {
        foreach ($schema as $tag => $data) {
            $tag = explode('#', $tag)[0];
            if (substr($tag, 0, 1) === "@") {
                continue;
            }

            if ($parent instanceof DOMDocument) {
                $element = $parent->createElement($tag, $data['@value'] ?? '');
            } else {
                $element = $parent->ownerDocument->createElement($tag, $data['@value'] ?? '');
            }

            foreach ($data['@attributes'] ?? [] as $attrKey => $attrValue) {
                $element->setAttribute($attrKey, $attrValue);
            }

            self::applyNodes($element, $data);

            if ($element->textContent === '') {
                continue;
            }

            if ($parent instanceof DOMDocument) {
                $parent->append($element);
            } else {
                $parent->appendChild($element);
            }
        }
    }
}
