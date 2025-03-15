import app from 'flarum/admin/app';
import { extName } from '../r';

app.initializers.add(extName, () => {
  app.extensionData
    .for(extName)
    .registerSetting({
      setting: `${extName}.recentDaysDefault`,
      label: app.translator.trans(`${extName}.admin.recentDaysDefault.label`),
      help: app.translator.trans(`${extName}.admin.recentDaysDefault.help`),
      type: `number`
    })
    .registerSetting({
      setting: `${extName}.limitDefault`,
      label: app.translator.trans(`${extName}.admin.limitDefault.label`),
      help: app.translator.trans(`${extName}.admin.limitDefault.help`),
      type: `number`
    })
    .registerSetting({
      setting: `${extName}.hotSpotHoursDefault`,
      label: app.translator.trans(`${extName}.admin.hotSpotHoursDefault.label`),
      help: app.translator.trans(`${extName}.admin.hotSpotHoursDefault.help`),
      type: `number`
    })
});
