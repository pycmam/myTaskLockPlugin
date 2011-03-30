<?php

class myTaskLockPluginConfiguration extends sfPluginConfiguration
{
  /**
   * @see sfPluginConfiguration
   */
  public function initialize()
  {
    $this->dispatcher->connect('command.pre_command', array($this, 'taskPreExecute'));
    $this->dispatcher->connect('command.post_command', array($this, 'taskPostExecute'));
  }


  /**
   * Task Pre Execute
   *
   * @param sfEvent $event
   */
  public function taskPreExecute(sfEvent $event)
  {
    $name = get_class($event->getSubject());
    $lockfile = $this->getLockfile($name);

    if ($this->isNeedLock($name) && file_exists($lockfile)) {
      $event->setProcessed(true);
      $event->setReturnValue(sprintf('Task %s already runned!', $name));

      return;
    }

    if ($this->isNeedLock($name) && !file_exists($lockfile)) {
      touch($lockfile);
    }
  }


  /**
   * Task Post Execute
   *
   * @param sfEvent $event
   */
  public function taskPostExecute(sfEvent $event)
  {
    $name = get_class($event->getSubject());
    $lockfile = $this->getLockfile($name);

    if ($this->isNeedLock($name) && file_exists($lockfile)) {
      unlink($lockfile);
    }
  }


  /**
   * Need lock?
   *
   * @param string $name
   * @return bool
   */
  protected function isNeedLock($name)
  {
    $watchedTasks = sfConfig::get('app_lock_tasks');

    return !$watchedTasks || in_array($name, $watchedTasks);
  }


  /**
   * Lockfile name
   *
   * @param string $name
   * @return string
   */
  protected function getLockfile($name)
  {
    return sprintf('%s/%s.lck', sfConfig::get('app_lockfile_dir'), $name);
  }
}