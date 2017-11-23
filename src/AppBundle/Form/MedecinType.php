<?php

namespace AppBundle\Form;

use AppBundle\Entity\Departement;
use AppBundle\Entity\Region;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormEvents;


class MedecinType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add( 'name' )
            ->add( 'region', EntityType::class, [
                'class' => 'AppBundle\Entity\Region',
                'placeholder' => 'Sélectionnez votre région',
                'mapped' => false,
                'required' => false
            ] );

        $builder->get( 'region' )->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) {
                $form = $event->getForm();
                $region = $event->getForm()->getData();
                $this->addDepartementField( $form->getParent(), $region );

            }
        );
    }

    /** Rajoute un champ ville au formulaire
     * @param FormInterface $form
     * @param Departement $departement
     */
    private function addVilleField(FormInterface $form, Departement $departement)
    {
        $form->add(
            'ville',
            EntityType::class,

            [
                'class' => 'AppBundle\Entity\Ville',
                'placeholder' => 'Sélectionnez votre ville',
                'choices' => $departement->getVilles()

            ]
        );


    }

    /** Rajoute un champ departement au formulaire
     * @param FormInterface $form
     * @param Region $region
     */
    private function addDepartementField(FormInterface $form, Region $region)
    {
        $builder = $form->getConfig()->getFormFactory()->createNamedBuilder(
            'departement',
            EntityType::class,
            null,
            [
                'class' => 'AppBundle\Entity\Departement',
                'placeholder' => 'Sélectionnez votre département',
                'mapped' => false,
                'required' => false,
                'auto_initialize' => false,
                'choices' => $region->getDepartements()

            ]
        );
        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) {
                $form = $event->getForm()->getParent();

                $dep = $event->getForm()->getData();

                if ($dep instanceof Departement) {


                    //  dump( $event->getData() );
                    $this->addVilleField( $form, $dep );
                }


            }
        );

        $form->add( $builder->getForm() );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults( array(
            'data_class' => 'AppBundle\Entity\Medecin'
        ) );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_medecin';
    }


}
