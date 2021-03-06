<?php
/**
 * @file
 * Bilag form.
 */

namespace AppBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\Bilag;
use Symfony\Component\Form\AbstractType;

/**
 * Class TiltagBilagType
 * @package AppBundle\Form
 */
class TiltagBilagType extends AbstractType {
  protected $bilag;

  public function __construct(Bilag $bilag) {
    $this->bilag = $bilag;
  }

  public function buildForm(FormBuilderInterface $builder, array $options) {
    $builder
      ->add('titel')
      ->add('kommentar')
      ->add('tiltag', 'entity', array(
        'class' => 'AppBundle:Tiltag',
        'label' => false,
        'attr' => array(
          'class' => 'hidden'
        )
      ))
      ->add('filepath', 'file', array(
        'data_class' => null,
        'attachment_path' => 'filepath',
      ));

    $builder->addEventListener(
      FormEvents::PRE_SUBMIT,
      function(FormEvent $event) {
        $data = $event->getData();
        $form = $event->getForm();

        // Remove filepath field if not submitted
        if (!isset($data['filepath']) || $data['filepath'] === null) {
          $form->remove('filepath');
        }
      });
  }

  public function configureOptions(OptionsResolver $resolver) {
    $resolver->setDefaults(array(
      'data_class' => 'AppBundle\Entity\Bilag'
    ));
  }

  public function getName() {
    return 'appbundle_tiltag_bilag';
  }
}
