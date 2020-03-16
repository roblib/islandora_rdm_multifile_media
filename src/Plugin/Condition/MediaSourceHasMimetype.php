<?php

namespace Drupal\islandora_rdm_multifile_media\Plugin\Condition;

use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

/**
 * Provides a 'Media Source' condition.
 *
 * @Condition(
 *   id = "media_source_mimetype",
 *   label = @Translation("Media Source Mimetype"),
 *   context = {
 *     "media" = @ContextDefinition("entity:media", required = FALSE, label =
 *   @Translation("Media"))
 *   }
 * )
 */
class MediaSourceHasMimetype extends ConditionPluginBase {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {


    $form['mimetype'] = [
      '#title' => $this->t('Source Media Mimetype'),
      '#type' => 'textfield',
      '#default_value' => $this->configuration['mimetype'],
    ];

    return parent::buildConfigurationForm($form, $form_state);;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['mimetype'] = $form_state->getValue('mimetype');
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}:q
   * 
   */
  public function evaluate() {
    foreach ($this->getContexts() as $context) {
      if ($context->hasContextValue()) {
        $entity = $context->getContextValue();
        $mid = $entity->id();
        if ($mid && !empty($this->configuration['mimetype'])) {
          $source = $entity->getSource();
          $config = $source->getConfiguration();
          $source_field_values = $entity->get($config['source_field'])->getValue();
          if ($source_field_values){
            $source_field_value = $source->getSourceFieldValue($entity);
            $source_file = File::load($source_field_value);
            if ($source_file && $this->configuration['mimetype'] == $source_file->getMimeType()) {
              return !$this->isNegated();
            }
          }
        }
      }
    }
    return $this->isNegated();
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    if (empty($this->configuration['mimetype'])) {
      return $this->t('No mimetype are selected.');
    }

    return $this->t(
      'Entity bundle in the list: @mimetype',
      [
        '@mimetype' => implode(', ', $this->configuration['field']),
      ]
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array_merge(
      ['mimetype' => []],
      parent::defaultConfiguration()
    );
  }

}
