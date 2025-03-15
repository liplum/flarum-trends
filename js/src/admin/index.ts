import app from 'flarum/admin/app';
import { extName } from '../r';

app.initializers.add(extName, () => {
  app.extensionData
    .for(extName)
    .registerSetting({
      setting: `${extName}.defaultRecentDays`,
      label: app.translator.trans(`${extName}.admin.defaultRecentDays.label`),
      help: app.translator.trans(`${extName}.admin.defaultRecentDays.help`),
      type: `number`
    })
    .registerSetting({
      setting: `${extName}.defaultLimit`,
      label: app.translator.trans(`${extName}.admin.defaultLimit.label`),
      help: app.translator.trans(`${extName}.admin.defaultLimit.help`),
      type: `number`
    })
    .registerSetting({
      setting: `${extName}.defaultHotSpotHours`,
      label: app.translator.trans(`${extName}.admin.defaultHotSpotHours.label`),
      help: app.translator.trans(`${extName}.admin.defaultHotSpotHours.help`),
      type: `number`
    })
});
