<?php

namespace OrgChart\Models;

class Person
{
    public $id;
    public $name;
    public $slug;
    public $href;
    public $title;
    public $adjacent;
    public $show_department;
    public $department;
    public $email;
    public $phone;
    public $location;
    public $parent;
    public $level;
    public $tags = [];
    public $nodes;

    public $text;

    /**
     * Person class constructor
     * 
     * @param WP_Post $person_post post of custom type 'person'
     */
    public function __construct($person_post, $link_to_path = null)
    {
        $this->id = $person_post->ID;
        $this->name = $person_post->post_title;
        $this->slug = $person_post->post_name;
        $this->parent = $person_post->post_parent;
        $this->href = '#' . $person_post->post_name;
        $this->level = $this->getLevel();
        $this->title = get_post_meta($this->id, '_person_title', true);
        $this->adjacent = get_post_meta($this->id, '_person_adjacent', true);
        $this->department = get_post_meta($this->id, '_person_department', true);
        $this->show_department = get_post_meta($this->id, '_person_show_department', true);
        $this->email = get_post_meta($this->id, '_person_email', true);
        $this->phone = get_post_meta($this->id, '_person_phone', true);
        $this->location = get_post_meta($this->id, '_person_location', true);
        $this->tags = get_the_tags($this->id) ? $this->tagObjectsToNames(get_the_tags($this->id)) : [];
        $this->text = $this->getTextTemplate($link_to_path);
    }

    /**
     * Add a Person to this Person's nodes array.
     * 
     * @param Person $person
     */
    public function addNode(Person $person)
    {
        if ($this->nodes === null) {
            $this->nodes = [];
        }

        if (!in_array($person, $this->nodes)) {
            $this->nodes[] = $person;
        }
    }

    /**
     * Gets the hierarchy level of this Person.
     * 
     * @return int : number of levels deep this Person is (starting at 1)
     */
    public function getLevel()
    {
        return count(get_post_ancestors($this->id)) + 1;
    }

    /**
     * Check to see if this Person it top-level
     * @return boolean
     */
    public function isTopLevel()
    {
        return $this->parent === 0;
    }

    /**
     * Converts an array of tag objects to an array of tag names
     * @param  array  $tag_objects [description]
     * @return [type]              [description]
     */
    protected function tagObjectsToNames(array $tag_objects)
    {
        $tags = [];

        foreach ($tag_objects as $tag_object) {
            if (stripos($this->name, $tag_object->name) === false) {
                $tags[] = $tag_object->name;
            }
        }

        return $tags;
    }

    /**
     * Gets the formatted template for the text field.
     * 
     * @return string
     */
    protected function getTextTemplate($link_to_path = null)
    {
        ob_start();
        require(plugin_dir_path(dirname(__FILE__)) . 'Views/re_mods-orgchart-person.php');

        return ob_get_clean();
    }

    /**
     * Returns an array of this Person's properties.
     * 
     * @return array
     */
    public function toArray()
    {
        return get_object_vars($this);
    }
}