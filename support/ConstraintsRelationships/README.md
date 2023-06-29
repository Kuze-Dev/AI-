# Constraints Relationships

Enforce referential Integrity On the Application

> This is particularly useful if you're using Planet Scale as a database as they don't allow foreign key constraints on their schemas.

## Usage

### OnDeleteCascade

To enforce a `CASCADE ON DELETE` to your models, you must add a `OnDeleteCascade` attribute to your model:

```php
#[OnDeleteCascade(['comments'])]
class Post extends Model
{
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
```

Now, when you try to delete a post, it's comments will be deleted as well.

> `OnDeleteCascade` doesn't support `BelongsTo` or `MorphTo` relationships as those relationship are child to parent. You are only allowed to delete from parent to child.

```php
#[OnDeleteCascade(['tags'])]
class Post extends Model
{
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }
}
```

For `BelongsToMany`/`MorphToMany` relationsips, only the pivot records will be deleted not the related model themselves. Deleting the related model directly could cause issues as there could be other Models that are related to them.

### OnDeleteRestrict

To enforce a `RESTRICT ON DELETE` to your models, you must add a `OnDeleteRestrict` attribute to your model:

```php
#[OnDeleteRestrict('users')]
class Role extends Model
{
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
```

Now when you try to delete a role with exisiting users, a `DeleteRestrictedException` will be thrown.
