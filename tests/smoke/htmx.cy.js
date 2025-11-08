// Cypress smoke tests for FR-002, FR-004.
describe('Taisafe-CDK Smoke', () => {
  const creds = {
    account: Cypress.env('ADMIN_ACCOUNT') || 'admin',
    password: Cypress.env('ADMIN_PASSWORD') || 'ChangeMe123!'
  };

  it('logs in and reaches dashboard', () => {
    cy.visit('/auth/login');
    cy.get('input[name="account"]').type(creds.account);
    cy.get('input[name="password"]').type(creds.password);
    cy.get('form').submit();
    cy.contains('批次').should('exist');
  });

  it('navigates to redeem partial', () => {
    cy.visit('/auth/login');
    cy.get('input[name="account"]').type(creds.account);
    cy.get('input[name="password"]').type(creds.password);
    cy.get('form').submit();
    cy.visit('/cdk/redeem');
    cy.contains('核銷').should('exist');
  });
});
