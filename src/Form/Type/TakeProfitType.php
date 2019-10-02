<?php

namespace App\Form\Type;

use App\Model\TakeProfit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class TakeProfitType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('price', MoneyType::class, [
                'currency' => false,
                'scale' => 10,
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Regex([
                        'pattern' => TradeType::PRICE_REGEX,
                        'normalizer' => static function ($value) {
                            return number_format($value, 18, '.', '');
                        },
                    ]),
                ],
            ])
            // TODO check this against min. nominal setting of symbol
            ->add('percentage', PercentType::class, [
                'type' => 'integer',
                'required' => true,
                'empty_data' => 0,
                'constraints' => [
                    new NotBlank(),
                    new GreaterThan(1),
                    new LessThanOrEqual(100),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TakeProfit::class,
        ]);
    }
}
