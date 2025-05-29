
CREATE OR REPLACE PROCEDURE loadSteadsFromFile(file_path TEXT)
    LANGUAGE plpgsql AS
$$
BEGIN
    INSERT INTO steads
    SELECT *
    FROM xmltable(
                 '/steads/stead'
                 PASSING xmlparse(DOCUMENT pg_read_file(file_path))
                 COLUMNS
                     ID Integer PATH '@ID',
                     OBJECTID Integer PATH '@OBJECTID',
                     OBJECTGUID char PATH '@OBJECTGUID',
                     CHANGEID Integer PATH '@CHANGEID',
                     NUMBER TEXT PATH '@NUMBER',
                     OPERTYPEID TEXT PATH '@OPERTYPEID',
                     PREVID Integer PATH '@PREVID',
                     NEXTID Integer PATH '@NEXTID',
                     UPDATEDATE date PATH '@UPDATEDATE',
                     STARTDATE date PATH '@STARTDATE',
                     ENDDATE date PATH '@ENDDATE',
                     ISACTUAL integer PATH '@ISACTUAL',
                     ISACTIVE integer PATH '@ISACTIVE'
             ) AS XML_STEADS;
END;
$$
;


call loadSteadsFromFile('AS_STEADS_20250522_f7ad2254-e87d-4a45-9384-2a69fd487936.XML');
