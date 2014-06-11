<?php

/**
 * This file is part of RollerworksSearch Component package.
 *
 * (c) 2012-2014 Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Component\Search;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 */
interface FieldTypeExtensionInterface
{
    /**
     * Builds the type.
     *
     * This method is called after the extended type has built the type to
     * further modify it.
     *
     * @see FieldTypeInterface::buildType()
     *
     * @param FieldConfigInterface $builder The config builder
     * @param array                $options The options
     */
    public function buildType(FieldConfigInterface $builder, array $options);

    /**
     * Builds the SearchFieldView.
     *
     * @param FieldConfigInterface $config
     * @param SearchFieldView      $view
     */
    public function buildFieldView(FieldConfigInterface $config, SearchFieldView $view);

    /**
     * Overrides the default options from the extended type.
     *
     * @param OptionsResolverInterface $resolver The resolver for the options.
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver);

    /**
     * Returns the name of the type being extended.
     *
     * @return string The name of the type being extended
     */
    public function getExtendedType();
}