<?php

namespace Drupal\coherence_core\Plugin\views\pager;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\pager\Mini;

/**
 * Pager plugin allowing a different number of items on the first page.
 *
 * @ingroup views_pager_plugins
 *
 * @ViewsPager(
 *   id = "views_flexible_mini_pager",
 *   title = @Translation("Paged output, mini pager, optional different number of items on first page"),
 *   short_title = @Translation("Flexible mini"),
 *   help = @Translation("Paged output, full pager, optional different number of items on first page"),
 *   theme = "views_mini_pager"
 * )
 */
class FlexibleMini extends Mini {

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['initial'] = [
      '#type' => 'number',
      '#title' => $this->t('Initial items'),
      '#description' => $this->t('The number of items to display on the first page. Enter 0 to use the same as items per page.'),
      '#default_value' => $this->options['initial'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Set first page items limit.
    $other_pages = $this->options['items_per_page'];
    $limit = !empty($this->options['initial']) ? $this->options['initial'] : $other_pages;
    $offset = !empty($this->options['offset']) ? $this->options['offset'] : 0;

    if ($this->current_page != 0) {
      $offset = $limit + (($this->current_page - 1) * $other_pages) + $offset;
      $limit = $other_pages;
    }

    // Re-implement functionality from parent, it calls parent::query() which
    // would undo our changes.
    // Only modify the query if we don't want to do a total row count
    if (!$this->view->get_total_rows) {
      // Don't query for the next page if we have a pager that has a limited
      // amount of pages.
      if ($this->getItemsPerPage() > 0 && (empty($this->options['total_pages']) || ($this->getCurrentPage() < $this->options['total_pages']))) {
        $limit += 1;
      }
    }

    $this->view->query->setLimit($limit);
    $this->view->query->setOffset($offset);
  }

}