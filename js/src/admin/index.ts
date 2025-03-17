import app from 'flarum/admin/app';
import { extName } from '../r';

app.initializers.add(extName, () => {
  app.extensionData
    .for(extName)
    .registerSetting({
      setting: `${extName}.defaultLimit`,
      label: app.translator.trans(`${extName}.admin.defaultLimit.label`),
      help: app.translator.trans(`${extName}.admin.defaultLimit.help`),
      type: `number`
    })
    .registerSetting({
      setting: `${extName}.commentWeight`,
      label: app.translator.trans(`${extName}.admin.commentWeight.label`),
      help: app.translator.trans(`${extName}.admin.commentWeight.help`),
      type: `number`
    })
    .registerSetting({
      setting: `${extName}.participantWeight`,
      label: app.translator.trans(`${extName}.admin.participantWeight.label`),
      help: app.translator.trans(`${extName}.admin.participantWeight.help`),
      type: `number`
    })
    .registerSetting({
      setting: `${extName}.viewWeight`,
      label: app.translator.trans(`${extName}.admin.viewWeight.label`),
      help: app.translator.trans(`${extName}.admin.viewWeight.help`),
      type: `number`
    })
    .registerSetting({
      setting: `${extName}.decayLambda`,
      label: app.translator.trans(`${extName}.admin.decayLambda.label`),
      help: app.translator.trans(`${extName}.admin.decayLambda.help`),
      type: `number`
    })
});
