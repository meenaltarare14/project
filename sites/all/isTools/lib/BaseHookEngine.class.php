<?php
/**
 * Class providing base function hooks
 *
 * @author clabarre
 */

/**
 *
 */
class BaseHookEngine {

  protected $hooks;

  const HOOK_ERROR = 'error';
  const HOOK_TRACE = 'trace';

  /**
   *
   */
  protected function __construct() {
    $this->hooks = array();
  }

  /**
   * Hooks:
   *   HOOK_ERROR
   *   HOOK_TRACE
   *
   * hook is func($arguments, ...) where $arguments are passed by reference for validate and execute
   *
   * callback returns 1 for valid, -1 for invalid, 0 for ignore
   *
   * @param string $type
   * @param string $newHook
   *
   * @return boolean
   */
  function addHook($type, $newHook) {
    // Detect already existing hook
    if (!empty($this->hooks[$type])) {
      foreach ($this->hooks[$type] as $hook) {
        if ($newHook === $hook) {
          // already attached
          return TRUE;
        }
      }
    }
    else {
      $this->hooks[$type] = array();
    }
    $this->hooks[$type][] = $newHook;
    return TRUE;
  }

  /**
   *
   * @param string $type
   * @param string $newHook
   *
   * @return boolean
   */
  function removeHook($type, $newHook) {
    // Detect already existing hook
    foreach ($this->hooks[$type] as $index => $hook) {
      if ($newHook === $hook) {
        // already attached
        unset($this->hooks[$type][$index]);
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   *
   * @param string $msg
   * @param number $code
   */
  protected function generateBasicHook($type, $msg, $code = 0) {
    if (!empty($this->hooks[$type])) {
      foreach ($this->hooks[$type] as $index => $callback) {
        call_user_func_array($callback, array($msg, $code));
      }
    }
  }

  /**
   *
   * @param string $type
   * @param string $data
   */
  protected function generateValidateHook($type, &$data) {
    if (!empty($this->hooks[$type])) {
      foreach ($this->hooks[$type] as $index => $callback) {
        $ret = call_user_func_array($callback, array(&$data));
        if ($ret < 0) {
          // detected that it is not valid
          return FALSE;
        }
        else {
          if ($ret > 0) {
            // detected that it is valid
            return TRUE;
          }
        }
      }
      // default to not valid
      return FALSE;
    }
    // No hooks to run
    return TRUE;
  }

  /**
   *
   * @param string $type
   * @param string $data
   */
  protected function generateExecuteHook($type, &$data) {
    // same interface
    return $this->generateValidateHook($type, $data);
  }

}

