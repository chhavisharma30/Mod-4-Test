<?php

namespace Drupal\buy_product\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class AddressForm to build buy product form.
 */
class AddressForm extends FormBase {

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * AddressForm constructor.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   */
  public function __construct(MessengerInterface $messenger, Connection $database) {
    $this->messenger = $messenger;
    $this->database = $database;
  }

  /**
   * Create function to build service objects through container.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   Container variable for building services objects.
   *
   * @return static
   *   Return static objects of services.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('messenger'),
      $container->get('database')
    );
  }

  /**
   * Function to get Form id.
   *
   * @return string
   *   Return form id.
   */
  public function getFormId() {
    return 'address_form';
  }

  /**
   * Function to build form.
   *
   * @param array $form
   *   Form variable array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Formstate variable for values storage.
   * @param mixed $node
   *   Node variable for node access.
   *
   * @return array|RedirectResponse
   *   Return the build form.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $node = NULL) {
    $current_user = \Drupal::currentUser();
    $uid = $current_user->id();

    // Checking if user address already exists.
    $query = $this->database->select('customer_address', 'ca')
      ->fields('ca', ['address'])
      ->condition('ca.uid', $uid)
      ->execute()
      ->fetchField();

    if ($query) {
      $this->messenger->addMessage($this->t('Address already exists. Your order has been placed successfully.'));
      return $this->redirectAfterBuy($node);
    }

    $form['address'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Address'),
      '#required' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Buy Product'),
    ];

    $form_state->setTemporaryValue('node', $node);

    return $form;
  }

  /**
   * Submit form function to handle form submission.
   *
   * @param array $form
   *   Form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state variable for form values.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Return the form response.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $current_user = \Drupal::currentUser();
    $uid = $current_user->id();
    $address = $form_state->getValue('address');
    $node = $form_state->getTemporaryValue('node');
    $title = $node->getTitle();

    // Insert query.
    $this->database->merge('customer_address')
      ->key(['uid' => $uid])
      ->fields(['address' => $address])
      ->execute();

    $this->messenger->addStatus($this->t("Order placed for $title successfully!"));

    return $this->redirectAfterBuy($node);
  }

  /**
   * Redirect function to redirect after product purchase.
   *
   * @param mixed $node
   *   Get product nodes.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Return the form response.
   */
  protected function redirectAfterBuy($node) {
    $url = Url::fromRoute('entity.node.canonical', ['node' => $node->id()]);
    $response = new RedirectResponse($url->toString());
    return $response;
  }
}
