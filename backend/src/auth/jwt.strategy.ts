import { Injectable, UnauthorizedException } from '@nestjs/common';
import { PassportStrategy } from '@nestjs/passport';
import { ExtractJwt, Strategy } from 'passport-jwt';
import { PrismaService } from '../prisma/prisma.service';

@Injectable()
export class JwtStrategy extends PassportStrategy(Strategy) {
  constructor(private prisma: PrismaService) {
    super({
      jwtFromRequest: ExtractJwt.fromAuthHeaderAsBearerToken(),
      ignoreExpiration: false,
      secretOrKey: process.env.JWT_SECRET || 'fuzurra-enterprise-jwt-secret-key-388274',
    });
  }

  async validate(payload: { sub: string; username: string; roleId: string; companyId: string }) {
    const user = await this.prisma.user.findUnique({
      where: { id: payload.sub },
      include: {
        role: {
          include: {
            permissions: true,
          },
        },
        company: true,
        branch: true,
      },
    });

    if (!user || !user.isActive) {
      throw new UnauthorizedException('User account inactive or invalid');
    }

    return {
      userId: user.id,
      username: user.username,
      fullName: user.fullName,
      email: user.email,
      companyId: user.companyId,
      branchId: user.branchId,
      role: user.role.name,
      permissions: user.role.permissions,
    };
  }
}
