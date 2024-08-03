### Index
- [Developer Guidelines](#developer-guidelines)
- [User Guidelines](#user-guidelines)

## Developer Guidelines

### For dev. environment

Run the following command for development environment.

```
composer update
npm install
npm run build
```

### For production environment
Run the following command for production environment to ignore the dev dependencies.

```
composer update --no-dev
npm install
npm run build
```

### Build Release
Set execution permission to the script file by `chmod +x bin/build.sh` command. Now, Run the following bash script.
```
bin/build.sh
```