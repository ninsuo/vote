A simple vote tool
===================

# What is it?

This is a simple vote tool that aims to be used during and after hackatons.

### Before the hackathon

The state of the vote place is "STOP", meaning that there are currently no votes. During that time, you can set-up your
prize categories (green, corporate, fun, usable...) and add the list of proposed projects.

### Before the project presentations show

Before anyone connects, set the application state to LIVE and put a waiting message. Then, voters connect to the
platform through a simple magic link. You can set-up a regular expression for the accepted emails (don't forget to
forbid "+" on gmail). Only a salted hash of the emails will be stored in the application.

### During the show

In LIVE state, you can choose which project is currently being presented, and your participants can rate them in then
given categories live. Don't forget to leave some time after every presentations to let people vote before going to the
next project.

### After the show

If your show was recorded to let missing participants watch it and vote afterward, they can do it when the application
is in VOTE state. Once the ceremony time has come, move the state to RANK to render the detailed results, and some
details on how they were calculated.

# Requirements

A PHP environment, MySQL or MariaDB, and an SMTP account.

# Installation

Install the project:

Download [composer](https://getcomposer.org/download/)

```
git clone git@github.com:ninsuo/vote.git
cd vote
composer install
```

Copy `.env` into `.env.local` and set up what's inside the `env.local` one.

# Usage

Download the [Symfony CLI](https://symfony.com/download)

Run the Symfony server:

```
symfony server:start
```

Once you created your first user, you may want to run:

```
./bin/console user:list
./bin/console user:admin <your uuid>
```
