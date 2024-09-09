<?php

namespace Drupal\custom_cart\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a custom "Add to Cart" form.
 */
class CustomCartForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'custom_cart_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['add_to_cart'] = [
      '#type' => 'submit',
      '#value' => t('Add to Cart'),
      '#attributes' => [
        'id' => ['add-to-cart-button'],
        'class' => ['button'],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Display a message using Drupal's Messenger service.
    \Drupal::messenger()->addMessage(t('Your product has been added to the cart.'));
  }
}
