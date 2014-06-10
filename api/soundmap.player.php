<?php

if (!class_exists('Soundmap_BasePlayer'))
{
    class Soundmap_BasePlayer{

        function __construct(){
            $this->register_hooks();
        }//__construct

        function register_hooks(){
            add_action('wp_enqueue_scripts', array($this, 'wp_enqueue_scripts'));
        }//register_hooks

        function wp_enqueue_scripts(){
            wp_enqueue_script('mediaelement');
        }//wp_enqueue_scripts

    }// class
}// if