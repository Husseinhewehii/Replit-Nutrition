describe('Entries Page - Current Day Styling and Expansion', () => {
  beforeEach(() => {
    // Login before each test
    cy.login()
    
    // Visit the entries page
    cy.visit('/entries')
  })

  it('should display current day with different styling from older days', () => {
    // Get today's date for comparison
    const today = new Date()
    const todayFormatted = today.toLocaleDateString('en-US', { 
      year: 'numeric', 
      month: 'long', 
      day: 'numeric' 
    })

    // Find the current day card
    cy.get('.card.current-day').should('exist')
    
    // Check that current day has the special styling
    cy.get('.card.current-day')
      .should('have.css', 'background-color')
      .and('match', /rgb\(240, 248, 255\)|#f0f8ff/) // Light blue background
    
    cy.get('.card.current-day')
      .should('have.css', 'border')
      .and('include', 'rgb(74, 144, 226)') // Blue border
    
    // Check that current day header has special styling
    cy.get('.card.current-day .day-header')
      .should('have.css', 'color')
      .and('match', /rgb\(44, 90, 160\)|#2c5aa0/) // Dark blue text
    
    cy.get('.card.current-day .day-header')
      .should('have.css', 'font-weight')
      .and('match', /600|bold/) // Bold font weight
    
    // Check that current day toggle icon has special color
    cy.get('.card.current-day .toggle-icon')
      .should('have.css', 'color')
      .and('match', /rgb\(44, 90, 160\)|#2c5aa0/) // Dark blue color
    
    // Verify the current day header shows today's date
    cy.get('.card.current-day .day-header')
      .should('contain.text', todayFormatted)
  })

  it('should only have current day expanded by default', () => {
    // Check that current day content is visible (not collapsed)
    cy.get('.card.current-day .day-content')
      .should('not.have.class', 'collapsed')
      .and('be.visible')
    
    // Check that current day toggle icon shows down arrow (expanded state)
    cy.get('.card.current-day .toggle-icon')
      .should('contain.text', '▼')
    
    // Check that all other day cards are collapsed
    cy.get('.card:not(.current-day)').each(($card) => {
      cy.wrap($card).within(() => {
        // Check that content has collapsed class
        cy.get('.day-content')
          .should('have.class', 'collapsed')
        
        // Check that toggle icon shows right arrow (collapsed state)
        cy.get('.toggle-icon')
          .should('contain.text', '▶')
      })
    })
  })

  it('should allow expanding and collapsing non-current days', () => {
    // Find a non-current day card
    cy.get('.card:not(.current-day)').first().as('oldDayCard')
    
    // Click on the header to expand
    cy.get('@oldDayCard').within(() => {
      cy.get('.day-header').click()
      
      // Check that content is now visible
      cy.get('.day-content')
        .should('not.have.class', 'collapsed')
        .and('be.visible')
      
      // Check that toggle icon changed to down arrow
      cy.get('.toggle-icon')
        .should('contain.text', '▼')
    })
    
    // Click again to collapse
    cy.get('@oldDayCard').within(() => {
      cy.get('.day-header').click()
      
      // Check that content is now collapsed
      cy.get('.day-content')
        .should('have.class', 'collapsed')
      
      // Check that toggle icon changed back to right arrow
      cy.get('.toggle-icon')
        .should('contain.text', '▶')
    })
  })

  it('should maintain current day styling when toggling other days', () => {
    // Toggle a non-current day
    cy.get('.card:not(.current-day)').first().within(() => {
      cy.get('.day-header').click()
    })
    
    // Verify current day still has special styling
    cy.get('.card.current-day')
      .should('have.css', 'background-color')
      .and('match', /rgb\(240, 248, 255\)|#f0f8ff/)
    
    cy.get('.card.current-day .day-header')
      .should('have.css', 'color')
      .and('match', /rgb\(44, 90, 160\)|#2c5aa0/)
    
    // Verify current day is still expanded
    cy.get('.card.current-day .day-content')
      .should('not.have.class', 'collapsed')
      .and('be.visible')
  })

  it('should display nutrition stats for current day when expanded', () => {
    // Check that current day shows nutrition statistics
    cy.get('.card.current-day .stats').should('be.visible')
    
    // Check for stat cards
    cy.get('.card.current-day .stat-card').should('have.length.at.least', 4)
    
    // Check for specific stat labels
    cy.get('.card.current-day .stat-label').should('contain.text', 'Calories')
    cy.get('.card.current-day .stat-label').should('contain.text', 'Protein')
    cy.get('.card.current-day .stat-label').should('contain.text', 'Carbs')
    cy.get('.card.current-day .stat-label').should('contain.text', 'Fat')
    
    // Check that nutrition table is visible
    cy.get('.card.current-day table').should('be.visible')
    cy.get('.card.current-day table thead').should('be.visible')
  })

  it('should not display nutrition stats for collapsed days', () => {
    // Check that collapsed days don't show their content
    cy.get('.card:not(.current-day)').first().within(() => {
      cy.get('.day-content.collapsed').should('not.be.visible')
      cy.get('.stats').should('not.be.visible')
      cy.get('table').should('not.be.visible')
    })
  })
})


