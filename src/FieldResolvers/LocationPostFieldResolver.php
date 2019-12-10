<?php
namespace PoP\LocationPosts\FieldResolvers;

use PoP\Translation\Facades\TranslationAPIFacade;
use PoP\ComponentModel\Schema\SchemaDefinition;
use PoP\ComponentModel\FieldResolvers\AbstractDBDataFieldResolver;
use PoP\ComponentModel\TypeResolvers\TypeResolverInterface;
use PoP\ComponentModel\Schema\TypeCastingHelpers;
use PoP\LocationPosts\TypeResolvers\LocationPostTypeResolver;

class LocationPostFieldResolver extends AbstractDBDataFieldResolver
{
    public static function getClassesToAttachTo(): array
    {
        return array(LocationPostTypeResolver::class);
    }

    public static function getFieldNamesToResolve(): array
    {
        return [
            'cats',
            'cat-slugs',
            'cat-name',
        ];
    }

    public function getSchemaFieldType(TypeResolverInterface $typeResolver, string $fieldName): ?string
    {
        $types = [
            'cats' => TypeCastingHelpers::combineTypes(SchemaDefinition::TYPE_ARRAY, SchemaDefinition::TYPE_ID),
            'cat-slugs' => TypeCastingHelpers::combineTypes(SchemaDefinition::TYPE_ARRAY, SchemaDefinition::TYPE_STRING),
            'cat-name' => SchemaDefinition::TYPE_STRING,
        ];
        return $types[$fieldName] ?? parent::getSchemaFieldType($typeResolver, $fieldName);
    }

    public function getSchemaFieldDescription(TypeResolverInterface $typeResolver, string $fieldName): ?string
    {
        $translationAPI = TranslationAPIFacade::getInstance();
        $descriptions = [
			'cats' => $translationAPI->__('', ''),
            'cat-slugs' => $translationAPI->__('', ''),
            'cat-name' => $translationAPI->__('', ''),
        ];
        return $descriptions[$fieldName] ?? parent::getSchemaFieldDescription($typeResolver, $fieldName);
    }

    public function resolveValue(TypeResolverInterface $typeResolver, $resultItem, string $fieldName, array $fieldArgs = [], ?array $variables = null, ?array $expressions = null, array $options = [])
    {
        $taxonomyapi = \PoP\Taxonomies\FunctionAPIFactory::getInstance();
        $locationpost = $resultItem;
        switch ($fieldName) {
            case 'cats':
                return $taxonomyapi->getPostTaxonomyTerms(
                    $typeResolver->getId($locationpost),
                    POP_LOCATIONPOSTS_TAXONOMY_CATEGORY,
                    [
                        'return-type' => POP_RETURNTYPE_IDS,
                    ]
                );

            case 'cat-slugs':
                return $taxonomyapi->getPostTaxonomyTerms(
                    $typeResolver->getId($locationpost),
                    POP_LOCATIONPOSTS_TAXONOMY_CATEGORY,
                    [
                        'return-type' => POP_RETURNTYPE_SLUGS,
                    ]
                );

            case 'cat-name':
                if ($cat = $typeResolver->resolveValue($resultItem, 'cat', $variables, $expressions, $options)) {
                    return $taxonomyapi->getTermName($cat, POP_LOCATIONPOSTS_TAXONOMY_CATEGORY);
                }
                return null;
        }

        return parent::resolveValue($typeResolver, $resultItem, $fieldName, $fieldArgs, $variables, $expressions, $options);
    }
}