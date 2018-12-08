# SymfonyRollbar bundle for Symfony Framework
[![Latest Stable Version](https://poser.pugx.org/oxcom/symfony-rollbar-bundle/v/stable)](https://packagist.org/packages/oxcom/symfony-rollbar-bundle)
[![Total Downloads](https://poser.pugx.org/oxcom/symfony-rollbar-bundle/downloads)](https://packagist.org/packages/oxcom/symfony-rollbar-bundle)
[![codecov](https://codecov.io/gh/OxCom/symfony-rollbar-bundle/branch/master/graph/badge.svg)](https://codecov.io/gh/OxCom/symfony-rollbar-bundle)
[![Build Status](https://travis-ci.org/OxCom/symfony-rollbar-bundle.svg?branch=master)](https://travis-ci.org/OxCom/symfony-rollbar-bundle)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

Bundle for Symfony Framework (2.8.x, 3.x, 4.x) that integrates Rollbar tracker

More documentation here [here](https://github.com/OxCom/symfony-rollbar-bundle/tree/master/Resources/doc)

# Install
1. Add bundle as dependency
    ```bash
    $ composer require oxcom/symfony-rollbar-bundle
    ```
2. Provide configuration for it
    ```yaml
    symfony_rollbar:
            enable: true
            exclude:
                - \AppBundle\Exceptions\MyAwesomeException
            rollbar:
                access_token: 'some-secret-token-here'
            rollbar_js:
                access_token: 'some-public-token-here'
    ```
3. Load bundle for 4.x:
    ```php
   \SymfonyRollbarBundle\SymfonyRollbarBundle::class => ['all' => true]
   ```
   or for 2.8.x and 3.x
   ```php
   $bundles = [
       // ...
       new \SymfonyRollbarBundle\SymfonyRollbarBundle(),
       // ...
   ];
   ```

# Bugs and Issues
Please, if You found a bug or something, that is not working properly, contact me and tell what's wrong. 
It's nice to have an example how to reproduce a bug, or any idea how to fix it in Your request. I'll take care about it ASAP.
