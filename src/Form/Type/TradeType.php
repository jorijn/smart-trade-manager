<?php

namespace App\Form\Type;

use App\Model\Symbol;
use App\Model\Trade;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

class TradeType extends AbstractType
{
    /** @var ObjectManager */
    protected $manager;

    /**
     * @param ObjectManager $manager
     */
    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $symbolChoices = array_reduce(
            $this->manager->getRepository(Symbol::class)->findAll(),
            static function (array $carry, Symbol $item) {
                $carry[$item->getSymbol()] = $item->getSymbol();

                return $carry;
            },
            []
        );

        $builder
            ->add('stoploss', MoneyType::class, [
                'currency' => false,
                'scale' => 10,
                'required' => false,
                'empty_data' => null,
                'constraints' => [
                    new Positive()
                ],
            ])
            ->add('symbol', ChoiceType::class, [
                'choices' => $symbolChoices,
                'required' => true,
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('quantity', MoneyType::class, [
                'currency' => false,
                'scale' => 10,
                'required' => true,
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('entryLow', MoneyType::class, [
                'currency' => false,
                'scale' => 10,
                'required' => true,
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('entryHigh', MoneyType::class, [
                'currency' => false,
                'scale' => 10,
                'required' => false,
                'empty_data' => null,
                'constraints' => [],
            ])
            ->add('takeProfits', CollectionType::class, [
                'entry_type' => TakeProfitType::class,
                'allow_add' => true,
                'allow_delete' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Trade::class,
        ]);
    }
}
