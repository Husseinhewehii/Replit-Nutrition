/// <reference types="cypress" />
// ***********************************************************
// This example plugins/index.js can be used to load plugins
//
// You can change the location of this file or turn off
// automatically serving support files with the
// 'supportFile' configuration option.
//
// You can read more here:
// https://on.cypress.io/plugins-guide
// ***********************************************************

// This function is called when a project is opened or re-opened (e.g. due to
// the project's config changing)

/**
 * @type {Cypress.PluginConfig}
 */
export default (on, config) => {
  // `on` is used to hook into various events Cypress emits
  // `config` is the resolved Cypress config

  // Task for seeding test data
  on('task', {
    'db:seed': () => {
      // This would typically run artisan commands to seed the database
      // For now, we'll just log that seeding should happen
      console.log('Database seeding should be run via artisan command')
      return null
    },
    'db:reset': () => {
      // This would typically run artisan commands to reset the database
      console.log('Database reset should be run via artisan command')
      return null
    }
  })
}


