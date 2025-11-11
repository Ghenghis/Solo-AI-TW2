TYPE=VIEW
query=select `wt`.`id_event` AS `id_event`,`wt`.`decommission` AS `decommission`,`wt`.`unit` AS `unit`,`wt`.`amount` AS `amount`,floor(((unix_timestamp() - `we`.`start`) / ((`we`.`finish` - `we`.`start`) / `wt`.`amount`))) AS `doneUnits` from (`twlan`.`world_event_train` `wt` join `twlan`.`world_event` `we` on((`we`.`id_event` = `wt`.`id_event`)))
md5=41704f051d7c05be0c43c63ece947b19
updatable=1
algorithm=0
definer_user=
definer_host=
suid=0
with_check_option=0
timestamp=2015-08-23 21:01:15
create-version=2
source=SELECT\n        wt.*,\n        FLOOR((UNIX_TIMESTAMP() - we.start) / ((we.finish - we.start) / amount)) AS doneUnits\n    FROM\n        world_event_train wt\n            INNER JOIN\n        world_event we ON we.id_event = wt.id_event\n;
client_cs_name=utf8
connection_cl_name=utf8_general_ci
view_body_utf8=select `wt`.`id_event` AS `id_event`,`wt`.`decommission` AS `decommission`,`wt`.`unit` AS `unit`,`wt`.`amount` AS `amount`,floor(((unix_timestamp() - `we`.`start`) / ((`we`.`finish` - `we`.`start`) / `wt`.`amount`))) AS `doneUnits` from (`twlan`.`world_event_train` `wt` join `twlan`.`world_event` `we` on((`we`.`id_event` = `wt`.`id_event`)))
mariadb-version=100020
