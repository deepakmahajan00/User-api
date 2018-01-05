<?php

require_once __DIR__.'/AppKernel.php';

class TestKernel extends AppKernel
{
    /**
     * @var \Closure
     */
    private $kernelModifier = null;

    /**
     * @var bool
     */
    private $resetKernelModifier = true;

    /**
     * @return mixed
     */
    public function boot()
    {
        $booted = parent::boot();

        if ($kernelModifier = $this->kernelModifier) {
            $kernelModifier($this);

            if ($this->resetKernelModifier) {
                $this->kernelModifier = null;
            }
        }

        return $booted;
    }

    /**
     * @param callable $kernelModifier
     */
    public function setKernelModifier(\Closure $kernelModifier)
    {
        $this->kernelModifier = $kernelModifier;
        $this->shutdown();
    }

    /**
     * Keep the altered kernel between two request
     *
     * @param bool $resetKernelModifier
     */
    public function setResetKernelModifier($resetKernelModifier)
    {
        $this->resetKernelModifier = $resetKernelModifier;
    }
}
