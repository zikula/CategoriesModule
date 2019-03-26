<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CategoriesModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\CategoriesModule\Entity\RepositoryInterface\CategoryRepositoryInterface;
use Zikula\CategoriesModule\Form\DataTransformer\CategoryTreeTransformer;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;

/**
 * Category tree form type class.
 */
class CategoryTreeType extends AbstractType
{
    use TranslatorTrait;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * CategoryTreeType constructor.
     *
     * @param TranslatorInterface $translator
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        TranslatorInterface $translator,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->setTranslator($translator);
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @param TranslatorInterface $translator
     */
    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new CategoryTreeTransformer($this->categoryRepository);
        $builder->addModelTransformer($transformer);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zikulacategoriesmodule_category_tree';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'locale' => 'en',
            'recurse' => true,
            'relative' => true,
            'includeRoot' => false,
            'includeLeaf' => false,
            'all' => false,
        ]);
        $resolver->setAllowedTypes('recurse', 'bool');
        $resolver->setAllowedTypes('relative', 'bool');
        $resolver->setAllowedTypes('includeRoot', 'bool');
        $resolver->setAllowedTypes('includeLeaf', 'bool');
        $resolver->setAllowedTypes('all', 'bool');

        $options['translator'] = $this->translator;
        $resolver->setNormalizer('label', function(Options $options, $label) {
            if (null === $label || empty($label)) {
                $isMultiple = $options['multiple'];
                $translator = $options['translator'];

                $label = $isMultiple ? $translator->__('Categories') : $translator->__('Category');
            }

            return $label;
        });
        $resolver->setNormalizer('placeholder', function(Options $options, $placeholder) {
            if (!$options['required']) {
                if (null === $placeholder || empty($placeholder)) {
                    $isMultiple = $options['multiple'];
                    $translator = $options['translator'];

                    $placeholder = $isMultiple ? $translator->__('Choose categories') : $translator->__('Choose a category');
                }
            }

            return $placeholder;
        });
        $resolver->setNormalizer('choices', function(Options $options, $choices) {
            if (empty($choices)) {
                $choices = $this->getCategoryChoices($options);
            }

            return $choices;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }

    /**
     * Returns choices for category selection.
     *
     * @return array
     */
    private function getCategoryChoices($options)
    {
        $locale = $options['locale'];
        $recurse = $options['recurse'] ?? true;
        $includeRoot = $options['includeRoot'] ?? false;
        $includeLeaf = $options['includeLeaf'] ?? false;
        $all = $options['all'] ?? false;

        $rootCategory = $this->categoryRepository->find(1);
        $children = $this->categoryRepository->getChildren($rootCategory, !$recurse, null, 'ASC', $includeRoot);

        $choices = [];
        foreach ($children as $child) {
            if (($child['is_leaf'] && !$includeLeaf) || ('I' === $child['status'] && $all)) {
                continue;
            }
            $indent = $child['lvl'] > 0 ? str_repeat('--', $child['lvl']) : '';
            if (isset($child['display_name'][$locale]) && !empty($child['display_name'][$locale])) {
                $catName = $child['display_name'][$locale];
            } else {
                $catName = $child['name'];
            }
            $choices['|' . $indent . ' ' . $catName] = $child['id'];
        }

        return $choices;
    }
}
