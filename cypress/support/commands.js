// ***********************************************
// This example commands.js shows you how to
// create various custom commands and overwrite
// existing commands.
//
// For more comprehensive examples of custom
// commands please read more here:
// https://on.cypress.io/custom-commands
// ***********************************************

/**
 * Custom command to login a user
 * @param {string} email - User email
 * @param {string} password - User password
 */
Cypress.Commands.add('login', (email = 'test@example.com', password = 'password') => {
  cy.session([email, password], () => {
    cy.visit('/login')
    cy.get('input[name="email"]').type(email)
    cy.get('input[name="password"]').type(password)
    cy.get('button[type="submit"]').click()
    cy.url().should('not.include', '/login')
  })
})

/**
 * Custom command to create test data for entries
 */
Cypress.Commands.add('createTestEntries', () => {
  // This would typically make API calls to create test data
  // For now, we'll assume the test data is created via database seeding
  cy.log('Test entries should be created via database seeding')
})

/**
 * Custom command to get today's date in the format used by the app
 */
Cypress.Commands.add('getTodayDate', () => {
  return cy.wrap(new Date().toISOString().split('T')[0])
})

/**
 * Custom command to get yesterday's date in the format used by the app
 */
Cypress.Commands.add('getYesterdayDate', () => {
  const yesterday = new Date()
  yesterday.setDate(yesterday.getDate() - 1)
  return cy.wrap(yesterday.toISOString().split('T')[0])
})


