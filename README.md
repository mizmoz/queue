
# Mizmoz Queue Library

A simple queue manager, still in early development so expect lots of changes to the core API, heartbreak likely.

## Requirements

PHP 7.1 or newer.

## Installation

```
# composer require mizmoz/queue
```

If you're using Beanstalkd you'll need the Pheanstalkd library

```
# composer require pda/pheanstalk
```

## Adapters

#### Beanstalkd

Beanstalkd queue using the Pheanstalkd library

#### Memory

In memory queue with SplQueue

#### Direct

This isn't a queue and will execute anything passed to it immediately.

## Usage

See the tests directory for usage. This will be updated as we get nearer a stable release product.