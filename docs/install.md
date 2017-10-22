# Install
1. Add SymfonyRollbarBundle with composer: ``` composer require oxcom/symfony-rollbar-bundle```
2. Register SymfonyRollbarBundle in AppKernel::registerBundles()
    ```php
        public function registerBundles()
        {
            $bundles = [
                // ...
                new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
                // ...
                new \SymfonyRollbarBundle\SymfonyRollbarBundle(),
                // ...
            ];
    
            return $bundles;
        }
    ```
3. Setup configuration
    ```php
    symfony_rollbar:
      enable: true
      rollbar:
        access_token: '%env(ROLLBAR_ACCESS_TOKEN)%'
        environment: '%kernel.environment%'
    ```