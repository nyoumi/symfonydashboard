# symfony dashboard
a symfony dashboard with authentication from an api (custom provider). 
- user log in this platform by providing login(phone number)/password. 
- symfony user auth utils make some verifications 
- user provider contact the API the to get authentication object in JSON or "NOT FOUND" response
- loginformauthenticator that overwrite authentication methods can verify if user can login
- user object have ROLES and user informations.
