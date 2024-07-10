---
extends: _layouts.post
section: content
title: BelongsToMany vs HasManyThrough
date: 2024-07-09
language: en
tags: Laravel, Eloquent
---
Laravel provides two abstractions to interact with a table that depends on an **intermediate** table: belongsToMany and hasManyThrough.

---

## BelongsToMany

<img src="/assets/img/contact-tags.png">

```php
class Contact extends Model
{
	public function tags()
	{
		return $this->belongsToMany(Tag::class);
	}
}

class Tag extends Model { }
```

This relation is used to model a **many-to-many** relation. The intermediate table has **both keys** to the tables being related. Using the schema above, we say that:

- A contact can have many tags or none;
- and a tag can have many contacts or none;

The **none** part is important because it implies that a contact may exists without a tag and vice-versa, e.g. a tag may exist without a contact.

This distinction does makes a difference when interacting with these models using Laravel Eloquent. It's possible to have both models persistent but not related.

```php
$contact = Contact::create();
$tag = Tag::create();
```

Then, to relate this 2 models we can use attach or sync relation methods. The difference being that sync remove all other tags that are related to contacts but was not informed and attach just adds a new related tag to the contact.

```php
$contact->tags()->attach($tag->id);
$contact->tags()->sync([$tag->id]);
```

Both methods **allows duplicates**. It's the programmer responsibility to filter duplicates before relating them to the model.

```php
$contact->tags()->attach($tag->id);
$contact->tags()->attach($tag->id); // duplicates the relation

$contact->tags()->sync([$tag->id, $tag->id]); // duplicates the relation
```

By using belongsToMany relation use gain following methods:

- sync                 
- syncWithoutDetaching 
- attach               
- detach               
- syncWithPivotValues  
- toggle               
- updateExistingPivot  

## HasManyThrough

<img src="/assets/img/project-deployments.png">

```php
class Project extends Model
{
	public function deployments()
	{
		return $this->hasManyThrough(Deployment::class, Environment::class);
	}
}

class Environment extends Model { }

class Deployment extends Model  { }
```

This relation is used to create an alias for **two one to many** relations that have some table in common. The intermediate table has a key for just __one__ of the tables being related. Using the schema above, we say that:

- A project has many environments
- An environment has many deployments
- And a project has many deployments through environments

It's important to note that only the project model can exist by its own. An environment model can only be created with an associated project - of course, the foreign key may be null and the environment could be created without a project, but that wouldn't make a relation.

```php
$project = Project::create();
$environment = Environment::create(['project_id' => $project->id]);
$deployment = Deployment::create(['environment_id' => $environmnet->id]);

$project->deployments()->count(); // === 1
```

This implies that every write operation using hasManyThrough must consider the intermediate table foreign key! So if I want to create a deployment through the deployments relation of the project I must inform the environment primary key. The same is true if I want to filter the deployments of a project by a specific environment.

```php
$project->deployments()->create(['environment_id' => $environment->id]);
$project->deployments()->where('environment_id', $environment->id)->delete();
$project->deployments()->whereIn('environment_id', [$environments->map->id])->get();
```

By using hasManyThrough we gain all hasMany methods.
# Wrapping up
- belongsToMany **attach / detaching / sync / toggle / etc**: This methods can only can used by many to many relations because they need that the related model had already been created.
- hasManyThrough **create / where / etc**: We need to inform the intermediate table primary key.
