## About Que Framework
Que is an evolving PHP framework focused on runtime speed. We believe PHP is much faster than people perceive it to be.
Que has tried to unleash the full speed of PHP while still offering the simplicity of a typical framework making development fun and easy.

> Frameworks create a layer on a programming language, making the language more friendly to use.
Already existing PHP frameworks create this same layer on PHP. 
But one thing common with these PHP frameworks is that they place
more priority on developer experience than runtime speed. 
Some of them even reduce PHP runtime speed by upto 30%, making people believe that PHP isn't fast enough.

## What Que did
Que put a layer on PHP but retained it's runtime speed, by optimizing much background processing which tends to slowdown PHP.

Que makes development seem easy by beautifully implementing such common tasks such as:

- Routing
- Dynamic database connection
- Multiple templating engine
- Multiple session, state and caching storage
- Centralized modeling
- Detailed error logging

We mentioned Que implementing multiple database connections, what do we mean by that? 
Well, Que has an infrastructure that allows you to connect to different database engines
using a single query syntax. This means that you can build a system using multiple database engines
and switch between them within your project. You can even, for instance, build a full project using MySQL and in production
your boss ask that you move to using MongoDB, without having to rewrite your queries, all have to do 
is switch Que's default database driver to MongoDB. However, for database engines that are currently not
supported natively by Que, Que provides you with an interface you can use to write your own database drive,
giving Que the ability to connect to all database engines supported by PHP using a single query syntax.

Que is organized, but still, Que adapts with disorganized developers. 
In other words, with Que's super-fast autoload engine, Que can locate your PHP files 
and include them in runtime no matter where you place these files within your project.
This also makes it possible for Que to stand alone, which means that a single Que engine can power multiple projects at the same time.

> **Que** is reliable, **Que** is fast, **Que** is PHP.
