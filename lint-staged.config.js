export default {
    "**/*": ["npm run spell:check:errors"],
    "**/*.php*": ["php vendor/bin/duster lint"],
    "**/*.{ts,tsx,js,jsx}": [
        // TODO add in linting scripts when React added
        // TODO add in formatting scripts when React added
    ],
};
