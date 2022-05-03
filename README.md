# Volunteer bot

A simple application I'm making for our volunteering center to manage people's orders using messengers. 
The idea is that presently all the orders are placed in a telegram channel and controlled manually based on comments, which is not very handy.

I'm going to run this bot (only for telegram at first, but can potentially be placed on facebook as well since there are a lot of people come from there) to collect messages in the channel, transform them and persist to DB for later usage via some king of web app. 

Messages can be either bot commands, or just a plain text, in both cases the app tries to identify what the message is about amd make proper actions (mostly changing the order statuses).

This module is framework-agnostic and can easily be driven by any of them, the only thing needs to be done is a working application and bootstrapping (implementation of interfaces, repositories and so on).

The entry point is [MessageProcessor class](app/Services/MessageProcessor.php).

In this example I use Symfony DI package for DI and Autowiring functions.

TBD: unit testing, CI.
