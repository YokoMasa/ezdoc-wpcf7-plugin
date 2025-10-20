<?php
namespace EZDocWpcf7;

function to_form_tag_name(string $attr_id): string {
  $first_three_letters = \substr($attr_id, 0, 3);
  if ($first_three_letters === 'ca:') {
    return 'ca_' . \substr($attr_id, 3);
  }
  return $attr_id;
}

function to_attr_id(string $form_tag_name): string {
  $first_three_letters = \substr($form_tag_name, 0, 3);
  if ($first_three_letters === 'ca_') {
    return 'ca:' . \substr($form_tag_name, 3);
  }
  return $form_tag_name;
}