import { TestBed } from '@angular/core/testing';

import { JoomlaUserService } from './joomla-user.service';

describe('JoomlaUserService', () => {
  beforeEach(() => TestBed.configureTestingModule({}));

  it('should be created', () => {
    const service: JoomlaUserService = TestBed.get(JoomlaUserService);
    expect(service).toBeTruthy();
  });
});
