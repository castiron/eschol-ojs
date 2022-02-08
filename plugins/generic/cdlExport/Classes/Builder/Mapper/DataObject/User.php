<?php namespace CdlExportPlugin\Builder\Mapper\DataObject;

class User extends AbstractDataObjectMapper {
    protected static $mapping = '
        userId ->                     id
                                      firstName
                                      middleName
                                      lastName
                                      initials
        localizedAffiliation ->       affiliation
                                      salutation
                                      gender
                                      email
                                      url
                                      phone
                                      fax
                                      mailingAddress
                                      country
        localizedBiography ->         biography
                                      interests
        localizedProfessionalTitle -> professionalTitle
        contactSignature ->           signature       
    ';
}