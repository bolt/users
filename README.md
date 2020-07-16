# Bolt Users Extension

The Bolt users extension allows you to add front-end users to your website.

Here is a list of things that the extension allows:

- Define groups of users, allow them to register and login
- Limit ContenTypes (pages) to be visible only by users belonging to a certain group
- Define user fields and allow them to edit their own profile

## Installation

To install this extension, simply run the following terminal command from your root folder:

```
composer require bolt/users
```

## Basic usage

To limit a a ContentType to a specific group of users, say `ROLE_MEMBER`, do the following:

1. Define your user group in `config/extensions/bolt-usersextension.yaml`:

```yaml
groups:
  ROLE_MEMBER:
    redirect_on_register: homepage # Provide either a route name, or a URL
    redirect_on_login: / # Provide either a route name, or a URL
    initial_status: enabled # Once a user registers, he/she is automatically allowed to login
```

2. Limit the access to a certain ContentType, e.g. `entries` to that user group in
`config/contenttypes.yaml`:

```yaml
entries:
    name: Entries
    singular_name: Entry
    fields:
        # ... normal ContentType definition
    allow_for_groups: [ 'ROLE_MEMBER', 'ROLE_ADMIN' ]
```

Note: The `allow_for_groups` option is used to limit access to the ContentType (listing
as well as record pages). It will only allow users who are logged in and have the
correct permission to access those pages. Not even admins will be allowed to view
those pages, hence why we add the `ROLE_ADMIN` group to ensure admins have view rights
too.

3. Allow users to register and to login

The extension allows you to include a registration form on any twig template.
To add a registration form, just add the following to your twig file:

```twig
    {{ registration_form(group='ROLE_MEMBER') }}
```

This line below will render a registration form with username, password and email
fields for the user to fill in. You must always specify the user group to which
this form applies (in this case, `ROLE_MEMBER`). Users who register with that group will
automatically receive access rights to ContentTypes limited to that group.

Currently, the `registration_form` function accepts the following options:

| Option name   | Description   | Required / optional  |
| ------------- |:-------------:| -----:|
| group         | The group for the registering user. Must match a group defined in the extension config. | required |
| withlabels    | If true, the `label` fields for each input will be included. Default is true.      |   optional |
| labels | An array used to override default labels. The key is the field name, e.g. `username` and the value is the label to be used. | optional |

To render the login form, use the following:

```twig
    {{ login_form() }}
```

The login function does not specify the group. The extension will try to authenticate the 
user with his/her credentials, and assign the correct group to that user. The `login_form`
function accepts two optional arguments, `withlabels` and `labels` which work the same way
as they do for the `registration_form` function.

## User profiles

Sometimes, you want to do more with users than simply restrict access to certain pages.
The extension allows you to define custom user fields by linking a ContentType to a
user group.

For example, to define a date of birth to our 'ROLE_MEMBER' group, we would do the following:

1. Define a `members` ContentType in `config/contenttypes.ymal` that will be used to store information about users.

```yaml
members:
    name: Members
    singular_name: Member
    title_format: "{author.username}"
    fields:
      dob:
        type: date
    viewless: true
```

Then, edit the extension config in `config/bolt-usersextension.yaml`:

```yaml
groups:
  ROLE_MEMBER:
    redirect_on_register: homepage
    redirect_on_login: /
    initial_status: enabled
    contenttype: members # Link the 'members' ContentType to the 'ROLE_MEMBER' group.
```

Now, users belonging to the `ROLE_MEMBER` group will be able to access their profile
at `/profile`. You can customize the appearance of this page by customizing the
record template for the members ContentType.

2. Optionally, you may wish to allow members to edit their profiles. To do this, add
the following to the config:

```yaml
groups:
  ROLE_MEMBER:
    redirect_on_register: homepage
    redirect_on_login: /
    initial_status: enabled
    contenttype: members
    allow_profile_edit: true # If true, members will be able to edit their profiels on /profile/edit . You must specify the edit template below
    profile_edit_template: 'edit_profile.twig'

```

In this case, the `edit_profile.twig` file, located in the `public/theme/your-theme/` directory,
may contain any regular twig template. Here is a basic example of the edit form that you
can include:

```twig
<form method="post">
    {% for field in record.fields %}
        <label for="fields[{{ field.name }}]"></label>
        {% if field.type === 'text' %}
            <input type="text" name="fields[{{ field.name }}]" value="{{ field.parsedValue }}" />
        {% elseif field.type === 'textarea' %}
            <textarea name="fields[{{ field.name }}]">{{ field.parsedValue }}</textarea>
        {% elseif field.type === 'checkbox' %}
            <input type="checkbox" name="fields[{{ field.name}}]" value="{{ field.parsedValue }}" />
        {% else if field.type === 'date' %}
            <input type="date" name="fields[{{ field.name }}]" value="{{ field.parsedValue }}" />
        {% endif %}
    {% endfor %}

    <!-- The input fields below are required for Bolt to process the form. Do not change them -->
    <input type="hidden" name="_csrf_token" value="{{ csrf_token('editrecord') }}">
    <input type="hidden" name="_edit_locale" value="{{ user.locale }}">
    <input type="hidden" name="status" value="published">
    <input type="submit" value="save">
</form>
```

## Customizing the register and login form appearance

If the customization options available in the `registration_form` and `login_form`
functions are not enough, you may wish to use the following functions:

For registration:

| Function      | Description   |
| ------------- |:-------------:|
| `registration_form_username`      | Renders the username field |
| `registration_form_password`      | Renders the password field |
| `registration_form_email`         | Renders the email field    |
| `registration_form_group`         | Renders a hidden field for the user's group. |
| `registration_form_csrf`          | Renders a hidden field that contains a CSRF token. |
| `registration_form_submit`        | Renders the submit button |

---

For logging in:

| Function      | Description   |
| ------------- |:-------------:|
| `login_form_username`      | Renders the username field |
| `login_form_password`      | Renders the password field |
| `registration_form_csrf`          | Renders a hidden field that contains a CSRF token. |
| `registration_form_submit`        | Renders the submit button |


Each field function above takes an optional `withlabel` argument and the `labels` argument
that is also used by `registration_form`.
