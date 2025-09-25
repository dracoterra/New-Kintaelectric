<?php
/**
 * Cargador de hooks del plugin WooCommerce Venezuela Suite
 *
 * @package WooCommerce_Venezuela_Suite
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para gestionar hooks del plugin
 */
class WCVS_Loader {

    /**
     * Array de hooks de WordPress
     *
     * @var array
     */
    protected $actions;

    /**
     * Array de filtros de WordPress
     *
     * @var array
     */
    protected $filters;

    /**
     * Constructor
     */
    public function __construct() {
        $this->actions = array();
        $this->filters = array();
    }

    /**
     * Añadir una acción de WordPress
     *
     * @param string $hook Hook de WordPress
     * @param object $component Componente que contiene la función
     * @param string $callback Función callback
     * @param int $priority Prioridad
     * @param int $accepted_args Número de argumentos aceptados
     */
    public function add_action($hook, $component, $callback, $priority = 10, $accepted_args = 1) {
        $this->actions = $this->add($this->actions, $hook, $component, $callback, $priority, $accepted_args);
    }

    /**
     * Añadir un filtro de WordPress
     *
     * @param string $hook Hook de WordPress
     * @param object $component Componente que contiene la función
     * @param string $callback Función callback
     * @param int $priority Prioridad
     * @param int $accepted_args Número de argumentos aceptados
     */
    public function add_filter($hook, $component, $callback, $priority = 10, $accepted_args = 1) {
        $this->filters = $this->add($this->filters, $hook, $component, $callback, $priority, $accepted_args);
    }

    /**
     * Añadir hook a la colección
     *
     * @param array $hooks Colección de hooks
     * @param string $hook Hook de WordPress
     * @param object $component Componente que contiene la función
     * @param string $callback Función callback
     * @param int $priority Prioridad
     * @param int $accepted_args Número de argumentos aceptados
     * @return array
     */
    private function add($hooks, $hook, $component, $callback, $priority, $accepted_args) {
        $hooks[] = array(
            'hook' => $hook,
            'component' => $component,
            'callback' => $callback,
            'priority' => $priority,
            'accepted_args' => $accepted_args
        );

        return $hooks;
    }

    /**
     * Ejecutar todos los hooks registrados
     */
    public function run() {
        foreach ($this->filters as $hook) {
            add_filter($hook['hook'], array($hook['component'], $hook['callback']), $hook['priority'], $hook['accepted_args']);
        }

        foreach ($this->actions as $hook) {
            add_action($hook['hook'], array($hook['component'], $hook['callback']), $hook['priority'], $hook['accepted_args']);
        }
    }
}
