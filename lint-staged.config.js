export default {
    "**/*.php*": ["vendor/bin/duster lint"],
    "**/*.{ts,tsx,js,jsx}": [
        "npm run spell:check:errors",
        // TODO uncomment when React has been installed
        // "npm run format:check",
        // "npm run lint:check:errors",
    ],
};
