UPGRADE
=======

## Upgrade FROM 1.0.0-beta4 to 1.0.0-beta5

There has been been some major refactoring to make the system more robust
and easier to use.

 * The `Rollerworks\Component\Search\Formatter\TransformFormatter` is removed,
   transforming is now performed in the InputProcessor.

 * The `Rollerworks\Component\Search\Mapping\Field` class is deprecated and will be
   removed in 2.0 use `Rollerworks\Component\Search\Metadata\SearchField` instead.

 * The methods `supportValueType()` and `setValueTypeSupport()` were added
   to `Rollerworks\Component\Search\FieldConfigInterface`. If you implemented
   this interface in your own code, you should add these two methods.

 * The methods `hasRangeSupport()` and `hasCompareSupport()` were removed
   from the `Rollerworks\Component\Search\FieldConfigInterface`. If you
   relied upon these methods you need to change your code.
   See description below for more details.

 * The methods `setRequired()` and `isRequired()` were removed
   from the `Rollerworks\Component\Search\FieldConfigInterface`.
   And marked deprecated in `\Rollerworks\Component\Search\SearchField`.

   Marking a field required has always been a bit unclear, *is the field required
   to exists in the condition or in exactly every group?*. Th disambiguation
   has been resolved by simple removing this functionality. If you do need this functionality,
   you can do this by validating the SearchCondition with a custom validator.

### Type

 * The options configuring of type has been changed to replace the deprecated
   OptionsResolverInterface. Second the method is renamed to "configureOptions".

   Before:

   ```php
   use Symfony\Component\OptionsResolver\OptionsResolverInterface;

   class TaskType extends AbstractFieldType
   {
       // ...
       public function setDefaultOptions(OptionsResolverInterface $resolver)
       {
           $resolver->setDefaults(array(
               'data_class' => 'App\Entity\Task',
           ));
       }
   }
   ```

   After:

   ```php
   use Symfony\Component\OptionsResolver\OptionsResolver;

   class TaskType extends AbstractFieldType
   {
       // ...
       public function configureOptions(OptionsResolver $resolver)
       {
           $resolver->setDefaults(array(
               'data_class' => 'App\Entity\Task',
           ));
       }
   }
   ```

 * Method `buildFieldView` has been renamed to `buildView` in both
  `Rollerworks\Component\Search\FieldTypeInterface` and
  `Rollerworks\Component\Search\FieldTypeExtensionInterface`.

 * Configuring whether a type supports ranges/comparisons and/or pattern-matchers
   now needs to be configured in the `buildType()` method.

  **Note:** The parent type may already have enabled support for the
  values-type, so you should not have to always enable this explicitly.

  Before:

  ```php
  use Symfony\Component\OptionsResolver\OptionsResolverInterface;

  class TaskType extends AbstractFieldType
  {
      public function hasRangeSupport()
      {
          return true;
      }
  }
  ```

  After:

  ```php
  use Symfony\Component\OptionsResolver\OptionsResolverInterface;

  class TaskType extends AbstractFieldType
  {
      public function buildType(FieldConfigInterface $config, array $options)
      {
          $config->setAcceptRange(true);
      }
  }
  ```

### Input

 * Input processors are made reusable, configuration must be passed as the
   first parameter of `Rollerworks\Component\Search\Input\InputProcessorInterface::process()`.

 * When the created search condition has errors an `InvalidSearchValuesException`
   will be thrown (after processing).

 * Validation of ranges (correct bounds) is now performed when processing
   the input (not after).

### Optimizers (former formatters)

Formatters are renamed to optimizers; The `Rollerworks\Component\Search\FormatterInterface`
is removed in favor of the new `Rollerworks\Component\Search\SearchConditionOptimizerInterface`.

### User error handling

Because it's possible that a search condition contains errors, each processor
that has a transforming or validating role will throw a
`Rollerworks\Component\Search\Exception\InvalidSearchValuesException`.

The `InvalidSearchValuesException` provides access to the actual search condition,
but the condition will contain some values that are invalid.

**Note:** The `InvalidSearchValuesException` is thrown *after* processing,
so it contains all invalid values (and not just the first violation) in a field's
values list.

A `ValuesGroup` object no longer keeps track of errors, getting the errors
is now done by asking each field in the group for there errors-state.

**Note:** By default only the fields at current level are checked,
pass `true` to `ValuesGroup::hasErrors()` to traverse the deeper
error-state of all nested groups.

### Validation

The default validator (Validator extension) is moved to `rollerworks/search-symfony-validator`,
and no longer implements the old Formatter interface.

**Caution:** To prevent clashes with other validators the namespace of the validator
is changed to: `Rollerworks\Component\Search\Extension\Symfony\Validator`.

### Values

 * Value objects `Compare`, `SingleValue`, `Range`, `PatternMatch` are now marked as
   final and made immutable. To change a value, remove the original at the index and
   add the new value object to the ValuesBag.

 * `PatternMatch::getViewValue()` was removed as this value-type has no view version.

 * All view-values are casted to strings now;
   When no view value is provided the "normalized" value must be castable to a string!

### SearchConditionSerializer

The `SearchConditionSerializer` no longer uses the static methods
but requires a `Rollerworks\Component\Search\FieldSetRegistryInterface` implementation
for loading FieldSets when unserializing.

**Note:** The FieldSet must be registered when serializing, this ensures the FieldSet is
known to the system and lessens the change of breakage when unserialize.

## Upgrade from 1.0.0-beta2 to 1.0.0-beta3

RollerworksSearch is split to multiple smaller packages,
each providing an extension for the RollerworksSearch 'core' package.

* rollerworks-search-doctrine-dbal: Doctrine DBAL Searching support
* rollerworks-search-doctrine-orm:  Doctrine ORM Searching support

* rollerworks-search-symfony-di: Symfony DependencyInjection extension (lazy loading)
* rollerworks-search-jms-metadata: JMS Metadata adapter extension

If you install any of the mentioned packages using composer,
the RollerworksSearch package will be automatic installed as well.

## UPGRADE FROM RollerworksRecordFilterBundle to RollerworksSearch

This project was formally called the RollerworksRecordFilterBundle.

If you like to switch to this new project please bare in mind
this library is completely rewritten, you should read the new documentation
to get started with the upgrade.

**Note:** The RollerworksRecordFilterBundle is discontinued, and will only receive
minor and security fixes until 2015. You are strongly advised to upgrade as soon as
possible.
