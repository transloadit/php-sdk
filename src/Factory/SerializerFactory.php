<?php

declare(strict_types=1);

namespace Transloadit\Factory;

use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

final class SerializerFactory
{
    public static function create(): SerializerInterface
    {
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $metadataAwareNameConverter = new MetadataAwareNameConverter(
            $classMetadataFactory,
            new CamelCaseToSnakeCaseNameConverter()
        );

        $objectNormalizer = new ObjectNormalizer($classMetadataFactory, $metadataAwareNameConverter);
        $normalizer = [new DateTimeNormalizer(['datetime_format' => 'Y/m/d H:i:s+00:00']), $objectNormalizer];
        $encoder = [new JsonEncoder()];

        return new Serializer($normalizer, $encoder);
    }
}
